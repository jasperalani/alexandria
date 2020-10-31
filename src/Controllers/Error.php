<?php

namespace Alexandria\Controllers;

class RequestError {

	public array $request;

	public function __construct() {
		$this->request = [
			'production_user' => $this->f('an unexpected error occurred')
		];
	}

	private function f (string $error){
		return $this->format($error);
	}

	private function format (string $error) {
		return ['error' => $error];
	}
}