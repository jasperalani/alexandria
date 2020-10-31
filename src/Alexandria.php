<?php

namespace Alexandria;

use Alexandria\Controllers\Retrieve;
use Alexandria\Controllers\Upload;
use Slim\App;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// todo: Add compression
// todo: check required directories exist for storage of images

class Alexandria {

	private App $app;
	public static string $environment;
	public static bool $compressionAvailable;

	public function __construct( bool $compressionAvailable, string $environment = 'production' ) {
		Alexandria::$compressionAvailable = $compressionAvailable;
		Alexandria::$environment          = $environment;

		$this->verifyDeploymentIntegrity();

		$this->app = AppFactory::create();
		$this->app->add( CorsMiddleware::class );
		$this->app->addRoutingMiddleware();

		$this->routes();
		$this->registerErrorHandling();

		$this->app->run();
	}

	public function routes() {
		// Upload image
		$this->app->post( '/upload', function ( Request $request, Response $response ) {
			$result = new Upload( $response, $request->getBody()->getContents() );

			return $result->getResponse();
		} );

		// Retrieve image
		$this->app->get( '/retrieve/{id}', function ( Request $request, Response $response, $args ) {
			$imageIdentifier = reset( $args );
			$result          = new Retrieve( $response, null, $imageIdentifier );

			if ( $result->success ) {
				$imageStream = $result->retrieveImageStream();

				return $response->withBody( $imageStream );
			}

			return $response;
		} );

		// Preflight Options Request
		$this->app->options( '', function ( Request $request, Response $response ): Response {
			return $response;
		} );
	}

	private function registerErrorHandling() {
		$displayErrorDetails = true;
		if ( 'production' === Alexandria::$environment ) {
			$displayErrorDetails = false;
		}
		$errorMiddleware = $this->app->addErrorMiddleware( $displayErrorDetails, true, true );
		if ( class_exists( 'Alexandria\ErrorRenderer' ) ) {
			$errorHandler = $errorMiddleware->getDefaultErrorHandler();
			$errorHandler->registerErrorRenderer( 'text/html', ErrorRenderer::class );
		}
	}

	private function verifyDeploymentIntegrity() {
		if ( ! is_dir( './Storage' ) ) {
			if ( ! mkdir( './Storage' ) ) {
				trigger_error( 'Alexandria: Failed to create Storage directory. Fatal.' );
				die();
			}
		}
		if ( ! is_dir( './Storage/Errors' ) ) {
			if ( ! mkdir( './Storage/Errors' ) ) {
				trigger_error( 'Alexandria: Failed to create Errors directory. Fatal.' );
				die();
			}
		}
		if ( ! is_dir( './Storage/Images' ) ) {
			if ( ! mkdir( './Storage/Images' ) ) {
				trigger_error( 'Alexandria: Failed to create Images directory. Fatal.' );
				die();
			}
		}

		return true;
	}
}