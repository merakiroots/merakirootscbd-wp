<?php

namespace MerakiCommerceCore;

use MerakiCommerceCore\Domain\COA\CoaAdminMetaBox;
use MerakiCommerceCore\Domain\COA\CoaMetaRegistrar;
use MerakiCommerceCore\Domain\COA\CoaMigrationCommand;
use MerakiCommerceCore\Domain\COA\CoaPostType;
use MerakiCommerceCore\Domain\COA\ProductCoaPanel;
use MerakiCommerceCore\Domain\Frontend\LabResultsQuery;
use MerakiCommerceCore\Domain\Frontend\LabResultsShortcode;
use MerakiCommerceCore\Domain\Frontend\ProductCoaPresenter;
use MerakiCommerceCore\Domain\ProductMeta\ProductMetaRegistrar;
use MerakiCommerceCore\Domain\Rest\RestFields;

final class Bootstrap {
    private ProductCoaPresenter $presenter;

    public function __construct() {
        $this->presenter = new ProductCoaPresenter();
    }

    public function register(): void {
        add_action( 'init', [ new CoaPostType(), 'register' ] );
        add_action( 'init', [ new ProductMetaRegistrar(), 'register' ] );
        add_action( 'init', [ new CoaMetaRegistrar(), 'register' ] );

        add_action( 'init', [ new CoaAdminMetaBox(), 'register' ] );
        add_action( 'init', [ new ProductCoaPanel( $this->presenter ), 'register' ] );

        add_action( 'init', [ $this->presenter, 'register' ] );
        add_action( 'init', [ new LabResultsShortcode( new LabResultsQuery( $this->presenter ) ), 'register' ] );

        add_action( 'rest_api_init', [ new RestFields( $this->presenter ), 'register' ] );

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            ( new CoaMigrationCommand( $this->presenter ) )->register();
        }
    }
}