<?php

namespace MerakiCommerceCore\Domain\COA;

use MerakiCommerceCore\Domain\Frontend\ProductCoaPresenter;

final class CoaMigrationCommand {
    private ProductCoaPresenter $presenter;
    private CoaRepository $repository;

    public function __construct( ProductCoaPresenter $presenter ) {
        $this->presenter  = $presenter;
        $this->repository = new CoaRepository();
    }

    public function register(): void {
        \WP_CLI::add_command( 'meraki coa migrate-legacy', [ $this, '__invoke' ] );
    }

    /**
     * @param array<int, string>   $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke( array $args, array $assoc_args ): void {
        $dry_run                    = $this->is_flag_enabled( $assoc_args, 'dry-run' );
        $create_missing_attachments = $this->is_flag_enabled( $assoc_args, 'create-missing-attachments' );
        $force_relink               = $this->is_flag_enabled( $assoc_args, 'force-relink' );
        $target_ids                 = CoaNormalizer::parse_product_id_csv( (string) ( $assoc_args['product_ids'] ?? '' ) );

        $query_args = [
            'post_type'      => 'product',
            'post_status'    => [ 'publish', 'draft', 'private' ],
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ];

        if ( ! empty( $target_ids ) ) {
            $query_args['post__in'] = $target_ids;
            $query_args['orderby']  = 'post__in';
        }

        $product_ids = get_posts( $query_args );

        if ( empty( $product_ids ) ) {
            \WP_CLI::warning( 'No matching products found for migration.' );
            return;
        }

        $stats = [
            'processed'             => 0,
            'migrated'              => 0,
            'skipped_no_legacy'     => 0,
            'skipped_already_linked'=> 0,
            'missing_attachment'    => 0,
            'created_coa_posts'     => 0,
            'dry_run_changes'       => 0,
        ];

        foreach ( $product_ids as $product_id ) {
            $product_id            = (int) $product_id;
            $stats['processed']++;

            $legacy_url            = (string) get_post_meta( $product_id, '_mr_coa_file', true );
            $legacy_url            = trim( $legacy_url );
            $existing_current_coa  = (int) get_post_meta( $product_id, '_mr_current_coa_id', true );

            if ( '' === $legacy_url ) {
                $stats['skipped_no_legacy']++;
                \WP_CLI::log( "Skip product #{$product_id}: no _mr_coa_file legacy URL." );
                continue;
            }

            if ( ! $force_relink && $existing_current_coa > 0 && 'mr_coa' === get_post_type( $existing_current_coa ) ) {
                $stats['skipped_already_linked']++;
                \WP_CLI::log( "Skip product #{$product_id}: already linked to COA #{$existing_current_coa}." );
                continue;
            }

            $attachment_id = $this->resolve_attachment_id( $legacy_url );
            if ( $attachment_id <= 0 && $create_missing_attachments && ! $dry_run ) {
                $attachment_id = $this->try_create_attachment_from_uploads_url( $legacy_url, $product_id );
            }
            if ( $attachment_id <= 0 ) {
                $stats['missing_attachment']++;
            }

            $coa_id = 0;
            if ( $existing_current_coa > 0 && 'mr_coa' === get_post_type( $existing_current_coa ) ) {
                $coa_id = $existing_current_coa;
            }
            if ( $coa_id <= 0 ) {
                $coa_id = $this->repository->find_by_legacy_url( $legacy_url );
            }

            $creating_new_coa = false;
            if ( $coa_id <= 0 ) {
                $creating_new_coa = true;
                $stats['created_coa_posts']++;

                if ( $dry_run ) {
                    $coa_id = 0;
                } else {
                    $coa_id = $this->create_coa_post( $product_id );
                    if ( $coa_id <= 0 ) {
                        \WP_CLI::warning( "Failed creating COA post for product #{$product_id}." );
                        continue;
                    }
                }
            }

            $planned = [
                'product_id'    => $product_id,
                'coa_id'        => $coa_id,
                'legacy_url'    => $legacy_url,
                'attachment_id' => $attachment_id,
                'would_create_coa' => $creating_new_coa,
            ];

            if ( $dry_run ) {
                $stats['dry_run_changes']++;
                \WP_CLI::log( 'DRY RUN: ' . wp_json_encode( $planned ) );
                continue;
            }

            update_post_meta( $coa_id, '_mr_legacy_coa_url', $legacy_url );
            update_post_meta( $coa_id, '_mr_coa_attachment_id', max( 0, (int) $attachment_id ) );
            update_post_meta( $coa_id, '_mr_coa_batch_id', (string) get_post_meta( $product_id, '_mr_coa_batch_id', true ) );
            update_post_meta( $coa_id, '_mr_coa_test_date', CoaNormalizer::normalize_date( (string) get_post_meta( $product_id, '_mr_coa_test_date', true ) ) );
            update_post_meta( $coa_id, '_mr_coa_lab_name', (string) get_post_meta( $product_id, '_mr_coa_lab_name', true ) );
            update_post_meta( $coa_id, '_mr_coa_status', 'current' );
            update_post_meta( $coa_id, '_mr_total_cbd', (string) get_post_meta( $product_id, '_mr_total_cbd', true ) );
            update_post_meta( $coa_id, '_mr_total_thc_status', (string) get_post_meta( $product_id, '_mr_total_thc_status', true ) );
            update_post_meta( $coa_id, '_mr_delta9_thc_status', (string) get_post_meta( $product_id, '_mr_delta9_thc_status', true ) );
            update_post_meta( $coa_id, '_mr_coa_category', (string) get_post_meta( $product_id, '_mr_coa_category', true ) );

            $this->repository->append_related_product( $coa_id, $product_id );

            update_post_meta( $product_id, '_mr_current_coa_id', $coa_id );

            $context = $this->presenter->get_coa_record( $coa_id );
            if ( '' !== $context['url'] ) {
                update_post_meta( $product_id, '_mr_coa_file', $context['url'] );
            }

            $stats['migrated']++;

            if ( $creating_new_coa ) {
                \WP_CLI::log( "Migrated product #{$product_id} -> new COA #{$coa_id}" );
            } else {
                \WP_CLI::log( "Migrated product #{$product_id} -> existing COA #{$coa_id}" );
            }
        }

        \WP_CLI::log( '--- Migration Summary ---' );
        foreach ( $stats as $key => $value ) {
            \WP_CLI::log( "{$key}: {$value}" );
        }

        if ( $dry_run ) {
            \WP_CLI::success( 'Dry-run completed. No writes were made.' );
            return;
        }

        \WP_CLI::success( 'Legacy COA migration completed.' );
    }

    /**
     * @param array<string, mixed> $assoc_args
     */
    private function is_flag_enabled( array $assoc_args, string $flag_name ): bool {
        if ( ! array_key_exists( $flag_name, $assoc_args ) ) {
            return false;
        }

        $value = $assoc_args[ $flag_name ];

        if ( true === $value || '' === $value || null === $value ) {
            return true;
        }

        if ( is_string( $value ) ) {
            $normalized = strtolower( trim( $value ) );
            return in_array( $normalized, [ '1', 'yes', 'true', 'on' ], true );
        }

        return (bool) $value;
    }

    private function create_coa_post( int $product_id ): int {
        $title = get_the_title( $product_id );
        if ( '' === $title ) {
            $title = 'COA';
        }

        $coa_id = wp_insert_post(
            [
                'post_type'   => 'mr_coa',
                'post_status' => 'publish',
                'post_title'  => sprintf( '%s COA', $title ),
            ]
        );

        if ( is_wp_error( $coa_id ) ) {
            return 0;
        }

        return (int) $coa_id;
    }

    private function resolve_attachment_id( string $legacy_url ): int {
        $legacy_url = trim( $legacy_url );
        if ( '' === $legacy_url ) {
            return 0;
        }

        foreach ( $this->build_attachment_lookup_candidates( $legacy_url ) as $candidate ) {
            $attachment_id = (int) attachment_url_to_postid( $candidate );
            if ( $attachment_id > 0 ) {
                return $attachment_id;
            }
        }

        $relative_upload_path = $this->extract_relative_upload_path( $legacy_url );
        if ( '' === $relative_upload_path ) {
            return 0;
        }

        return $this->find_attachment_id_by_relative_upload_path( $relative_upload_path );
    }

    /**
     * @return array<int, string>
     */
    private function build_attachment_lookup_candidates( string $legacy_url ): array {
        $legacy_url = trim( $legacy_url );
        if ( '' === $legacy_url ) {
            return [];
        }

        $uploads = wp_upload_dir();
        $baseurl = rtrim( (string) ( $uploads['baseurl'] ?? '' ), '/' );
        $siteurl = rtrim( home_url( '/' ), '/' );

        $candidates = [];
        $append = static function ( string $candidate ) use ( &$candidates ): void {
            $candidate = trim( $candidate );
            if ( '' === $candidate || in_array( $candidate, $candidates, true ) ) {
                return;
            }
            $candidates[] = $candidate;
        };

        $append( $legacy_url );

        $has_scheme = 1 === preg_match( '#^[a-z][a-z0-9+\-.]*://#i', $legacy_url );
        if ( ! $has_scheme ) {
            if ( str_starts_with( $legacy_url, '//' ) ) {
                $site_scheme = (string) parse_url( $siteurl, PHP_URL_SCHEME );
                if ( '' !== $site_scheme ) {
                    $append( $site_scheme . ':' . $legacy_url );
                }
            } elseif ( '' !== $siteurl ) {
                if ( str_starts_with( $legacy_url, '/' ) ) {
                    $append( $siteurl . $legacy_url );
                } else {
                    $append( $siteurl . '/' . ltrim( $legacy_url, '/' ) );
                }
            }
        }

        $relative_upload_path = $this->extract_relative_upload_path( $legacy_url );
        if ( '' !== $relative_upload_path && '' !== $baseurl ) {
            $append( $baseurl . '/' . ltrim( $relative_upload_path, '/' ) );
        }

        return $candidates;
    }

    private function extract_relative_upload_path( string $legacy_url ): string {
        $legacy_url = trim( $legacy_url );
        if ( '' === $legacy_url ) {
            return '';
        }

        $path = (string) parse_url( $legacy_url, PHP_URL_PATH );
        if ( '' === $path ) {
            $path = $legacy_url;
        }

        $path = str_replace( '\\', '/', $path );
        $path = preg_replace( '#/+#', '/', $path ) ?: $path;
        $path = ltrim( $path, '/' );

        $uploads = wp_upload_dir();
        $baseurl = (string) ( $uploads['baseurl'] ?? '' );
        $baseurl_path = trim( (string) parse_url( $baseurl, PHP_URL_PATH ), '/' );

        $prefixes = [ 'wp-content/uploads/' ];
        if ( '' !== $baseurl_path ) {
            $prefixes[] = $baseurl_path . '/';
        }

        foreach ( $prefixes as $prefix ) {
            if ( str_starts_with( $path, $prefix ) ) {
                return ltrim( substr( $path, strlen( $prefix ) ), '/' );
            }
        }

        if ( ! str_contains( $path, 'wp-content/' ) && ! str_contains( $legacy_url, '://' ) ) {
            return ltrim( $path, '/' );
        }

        return '';
    }

    private function find_attachment_id_by_relative_upload_path( string $relative_upload_path ): int {
        global $wpdb;

        $relative_upload_path = ltrim( str_replace( '\\', '/', trim( $relative_upload_path ) ), '/' );
        if ( '' === $relative_upload_path ) {
            return 0;
        }

        $post_id = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1",
                $relative_upload_path
            )
        );
        if ( $post_id > 0 ) {
            return $post_id;
        }

        $post_id = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1",
                '/' . $relative_upload_path
            )
        );
        if ( $post_id > 0 ) {
            return $post_id;
        }

        return 0;
    }

    private function try_create_attachment_from_uploads_url( string $legacy_url, int $product_id ): int {
        $uploads = wp_upload_dir();
        $basedir = (string) ( $uploads['basedir'] ?? '' );
        $relative_path = $this->extract_relative_upload_path( $legacy_url );

        if ( '' === $basedir || '' === $relative_path ) {
            return 0;
        }

        $file_path = trailingslashit( $basedir ) . ltrim( $relative_path, '/' );

        if ( ! file_exists( $file_path ) ) {
            return 0;
        }

        $file_type = wp_check_filetype( basename( $file_path ), null );
        $attachment_post = [
            'post_mime_type' => (string) ( $file_type['type'] ?? 'application/pdf' ),
            'post_title'     => sanitize_file_name( pathinfo( $file_path, PATHINFO_FILENAME ) ),
            'post_status'    => 'inherit',
        ];

        $attachment_id = wp_insert_attachment( $attachment_post, $file_path, $product_id );
        if ( is_wp_error( $attachment_id ) || $attachment_id <= 0 ) {
            return 0;
        }

        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
        if ( is_array( $metadata ) ) {
            wp_update_attachment_metadata( $attachment_id, $metadata );
        }

        return (int) $attachment_id;
    }
}
