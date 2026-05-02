<?php

namespace MerakiCommerceCore\Domain\Frontend;

final class ProductCoaPresenter {
    public function register(): void {
        // The launch bridge theme registers a legacy COA renderer.
        // Remove it so plugin-owned normalized data becomes authoritative.
        remove_action( 'woocommerce_single_product_summary', 'mr_render_product_coa_callout', 31 );

        if ( ! has_action( 'woocommerce_single_product_summary', [ $this, 'render_single_product_callout' ] ) ) {
            add_action( 'woocommerce_single_product_summary', [ $this, 'render_single_product_callout' ], 31 );
        }

        add_filter( 'woocommerce_product_tabs', [ $this, 'filter_product_tabs' ], 1000 );
    }

    /**
     * @return array{coa_id:int,url:string,lab_name:string,test_date:string,status:string,batch_id:string,total_cbd:string,total_thc_status:string,delta9_thc_status:string}
     */
    public function get_product_context( int $product_id ): array {
        $coa_id = (int) get_post_meta( $product_id, '_mr_current_coa_id', true );

        if ( $coa_id > 0 && 'mr_coa' === get_post_type( $coa_id ) ) {
            $record            = $this->get_coa_record( $coa_id );
            $record['coa_id']  = $coa_id;
            return $record;
        }

        $legacy_url = (string) get_post_meta( $product_id, '_mr_coa_file', true );

        return [
            'coa_id'            => 0,
            'url'               => $legacy_url,
            'lab_name'          => (string) get_post_meta( $product_id, '_mr_coa_lab_name', true ),
            'test_date'         => (string) get_post_meta( $product_id, '_mr_coa_test_date', true ),
            'status'            => 'current',
            'batch_id'          => (string) get_post_meta( $product_id, '_mr_coa_batch_id', true ),
            'total_cbd'         => (string) get_post_meta( $product_id, '_mr_total_cbd', true ),
            'total_thc_status'  => (string) get_post_meta( $product_id, '_mr_total_thc_status', true ),
            'delta9_thc_status' => (string) get_post_meta( $product_id, '_mr_delta9_thc_status', true ),
        ];
    }

    /**
     * @return array{url:string,lab_name:string,test_date:string,status:string,batch_id:string,total_cbd:string,total_thc_status:string,delta9_thc_status:string}
     */
    public function get_coa_record( int $coa_id ): array {
        $attachment_id = (int) get_post_meta( $coa_id, '_mr_coa_attachment_id', true );
        $legacy_url    = (string) get_post_meta( $coa_id, '_mr_legacy_coa_url', true );

        $url = '';
        if ( $attachment_id > 0 ) {
            $url = (string) wp_get_attachment_url( $attachment_id );
        }
        if ( '' === $url ) {
            $url = $legacy_url;
        }

        return [
            'url'               => $url,
            'lab_name'          => (string) get_post_meta( $coa_id, '_mr_coa_lab_name', true ),
            'test_date'         => (string) get_post_meta( $coa_id, '_mr_coa_test_date', true ),
            'status'            => (string) get_post_meta( $coa_id, '_mr_coa_status', true ),
            'batch_id'          => (string) get_post_meta( $coa_id, '_mr_coa_batch_id', true ),
            'total_cbd'         => (string) get_post_meta( $coa_id, '_mr_total_cbd', true ),
            'total_thc_status'  => (string) get_post_meta( $coa_id, '_mr_total_thc_status', true ),
            'delta9_thc_status' => (string) get_post_meta( $coa_id, '_mr_delta9_thc_status', true ),
        ];
    }

    public function render_single_product_callout(): void {
        if ( ! function_exists( 'is_product' ) || ! is_product() ) {
            return;
        }

        $product_id = get_the_ID();
        if ( $product_id <= 0 ) {
            return;
        }

        $context = $this->get_product_context( $product_id );
        if ( '' === $context['url'] ) {
            return;
        }

        $lab_name  = '' !== $context['lab_name'] ? $context['lab_name'] : __( 'Third-party lab', 'meraki-commerce-core' );
        $test_date = $context['test_date'];

        ?>
        <div class="mr-coa-callout" data-mr-source="meraki-commerce-core">
            <div>
                <span class="mr-kicker"><?php esc_html_e( 'Third-Party Lab Result', 'meraki-commerce-core' ); ?></span>
                <h3><?php esc_html_e( 'Know what is in your CBD.', 'meraki-commerce-core' ); ?></h3>
                <dl class="mr-coa-callout__meta">
                    <div><dt><?php esc_html_e( 'Lab', 'meraki-commerce-core' ); ?></dt><dd><?php echo esc_html( $lab_name ); ?></dd></div>
                    <?php if ( '' !== $test_date ) : ?>
                        <div><dt><?php esc_html_e( 'Date', 'meraki-commerce-core' ); ?></dt><dd><?php echo esc_html( $test_date ); ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>
            <a class="mr-button" href="<?php echo esc_url( $context['url'] ); ?>" target="_blank" rel="noopener">
                <?php esc_html_e( 'View COA', 'meraki-commerce-core' ); ?>
            </a>
        </div>
        <?php
    }

    /**
     * @param array<string, array<string, mixed>> $tabs
     * @return array<string, array<string, mixed>>
     */
    public function filter_product_tabs( array $tabs ): array {
        $tabs['lab_results'] = [
            'title'    => __( 'Lab Results', 'meraki-commerce-core' ),
            'priority' => $tabs['lab_results']['priority'] ?? 50,
            'callback' => [ $this, 'render_product_tab_lab_results' ],
        ];

        return $tabs;
    }

    public function render_product_tab_lab_results(): void {
        $this->render_single_product_callout();
    }
}
