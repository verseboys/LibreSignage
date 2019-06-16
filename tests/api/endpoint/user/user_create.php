<?php

use JsonSchema\Validator;
use classes\APITestCase;
use classes\APITestUtils;

class user_create extends APITestCase {
	use traits\TestEndpointNotAuthorizedWithoutLogin;

	const UNIT_TEST_USER = 'unit_test_user';

	public function setUp(): void {
		parent::setUp();

		$this->set_endpoint_method('POST');
		$this->set_endpoint_uri('user/user_create.php');
	}

	public function test_endpoint_not_authorized_for_non_admin_users(): void {
		$this->api->login('user', 'user');

		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[
				'user' => self::UNIT_TEST_USER,
				'groups' => ['editor', 'display']
			],
			[],
			TRUE
		);
		$this->assert_api_errored(
			$resp,
			$this->api->get_error_code('API_E_NOT_AUTHORIZED')
		);

		$this->api->logout();
	}

	/**
	 * @dataProvider params_provider
	 */
	public function test_fuzz_params(
		array $params,
		string $error
	): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			$params,
			[],
			TRUE
		);
		if ($error === 'API_E_OK'){
			$this->assertEquals(
				$this->api->get_error_code('API_E_OK'),
				$resp->error
			);
		} else {
			$this->assert_api_errored($resp, $this->api->get_error_code($error));
		}

		$this->api->logout();
	}

	public function params_provider(): array {
		return [
			'Valid parameters' => [
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => ['editor', 'display']
				],
				'API_E_OK'
			],
			'Empty username' => [
				[
					'user' => '',
					'groups' => ['editor', 'display']
				],
				'API_E_LIMITED'
			],
			'NULL username' => [
				[
					'user' => NULL,
					'groups' => ['editor', 'display']
				],
				'API_E_INVALID_REQUEST'
			],
			'No groups parameter' => [
				[
					'user' => self::UNIT_TEST_USER
				],
				'API_E_OK'
			],
			'Empty groups array' => [
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => []
				],
				'API_E_OK'
			],
			'NULL groups parameter' => [
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => NULL
				],
				'API_E_OK'
			],
			'Empty group name in groups array' => [
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => ['']
				],
				'API_E_LIMITED'
			],
			'No parameters' => [
				[],
				'API_E_INVALID_REQUEST'
			]
		];
	}

	public function test_invalid_request_error_on_existing_user(): void {
		$this->api->login('admin', 'admin');

		$resp = NULL;
		for ($i = 0; $i < 2; $i++) {
			$resp = $this->api->call(
				$this->get_endpoint_method(),
				$this->get_endpoint_uri(),
				[
					'user' => self::UNIT_TEST_USER,
					'groups' => ['editor', 'display']
				],
				[],
				TRUE
			);
		}

		$this->assert_api_errored(
			$resp,
			$this->api->get_error_code('API_E_INVALID_REQUEST')
		);

		$this->api->logout();
	}

	public function test_is_response_schema_correct(): void {
		$this->api->login('admin', 'admin');

		$resp = $this->api->call(
			$this->get_endpoint_method(),
			$this->get_endpoint_uri(),
			[
				'user' => self::UNIT_TEST_USER,
				'groups' => ['editor', 'display']
			],
			[],
			TRUE
		);
		$this->assert_valid_json(
			$resp,
			dirname(__FILE__).'/schemas/user_create.schema.json'
		);

		$this->api->logout();
	}

	public function tearDown(): void {
		$this->api->login('admin', 'admin');
		$this->api->call(
			'POST',
			'user/user_remove.php',
			[ 'user' => self::UNIT_TEST_USER ],
			[],
			true
		);
		$this->api->logout();
	}
}
