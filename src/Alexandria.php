<?php

namespace Alexandria;

use Alexandria\Controllers\Upload;
use mysqli;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Stream;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

// todo: do i need an alias for $databaseInstance ?
// todo: Add compression
// todo: check required directories exist for storage of images

class Alexandria {

	private App $app;
	public static string $environment;

	public function __construct( $environment = 'production' ) {
		Alexandria::$environment = $environment;

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
		$this->app->get( '/{id}', function ( Request $request, Response $response, $args ) {
			$getId  = "SELECT * FROM images WHERE id = " . $args['id'];
			$result = Alexandria::$db->query( $getId );

			$image = $result->fetch_assoc();

			if ( ! $result ) {
				return Alexandria::$response->response(
					[ 'error' => 'failed to query database' ],
					$response
				);
			}


			$file     = "images/" . $image['id'] . '.' . $image['ext'];
			$openFile = fopen( $file, 'rb' );
			$stream   = new Stream( $openFile );

			return $response->withBody( $stream )
			                ->withHeader( 'Access-Control-Allow-Origin', '*' )
			                ->withHeader( 'Access-Control-Allow-Headers',
				                'X-Requested-With, Content-Type, Accept, Origin, Authorization' )
			                ->withHeader( 'Access-Control-Allow-Methods',
				                'GET, POST, PUT, DELETE, PATCH, OPTIONS' );
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

}