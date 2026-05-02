<?php

namespace MerakiCommerceCore\Domain\Frontend;

final class LabResultsShortcode {
    private LabResultsQuery $query;

    public function __construct( LabResultsQuery $query ) {
        $this->query = $query;
    }

    public function register(): void {
        add_shortcode( 'meraki_lab_results', [ $this, 'render' ] );
    }

    public function render(): string {
        $groups = $this->query->get_grouped_records();

        if ( empty( $groups ) ) {
            return '<p>' . esc_html__( 'No COA records are currently available.', 'meraki-commerce-core' ) . '</p>';
        }

        ob_start();
        ?>
        <div class="mr-lab-results-list" data-mr-source="meraki-commerce-core">
            <?php foreach ( $groups as $form => $records ) : ?>
                <section class="mr-lab-group">
                    <h2><?php echo esc_html( $form ); ?></h2>
                    <div class="mr-lab-group__rows">
                        <?php foreach ( $records as $record ) : ?>
                            <article class="mr-lab-row">
                                <div>
                                    <h3>
                                        <?php if ( '' !== $record['product_url'] ) : ?>
                                            <a href="<?php echo esc_url( $record['product_url'] ); ?>"><?php echo esc_html( $record['product_name'] ); ?></a>
                                        <?php else : ?>
                                            <?php echo esc_html( $record['product_name'] ); ?>
                                        <?php endif; ?>
                                    </h3>
                                    <p>
                                        <?php echo esc_html( '' !== $record['lab_name'] ? $record['lab_name'] : __( 'Third-party lab', 'meraki-commerce-core' ) ); ?>
                                        <?php if ( '' !== $record['test_date'] ) : ?>
                                            · <?php echo esc_html( $record['test_date'] ); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <a class="mr-button" href="<?php echo esc_url( $record['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View COA', 'meraki-commerce-core' ); ?></a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}