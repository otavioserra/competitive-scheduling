<?php

if ( ! class_exists( 'Authentication' ) ) {
	class Authentication {

		/**
		 * Install plugin SSL keys containing 'public' and 'private' keys and 'private-password' on success.
		 *
		 * @return void
		 */
		public static function install_keys() {
			$privatePassword = md5( uniqid( rand(), true ) );

			$keys = self::generate_keys(
				array(
					'type'     => 'RSA',
					'password' => $privatePassword,
				)
			);

			if ( $keys ) {
				add_option(
					'competitive_scheduling_openssl_keys',
					array(
						'public'           => $keys['public'],
						'private'          => $keys['private'],
						'private-password' => $privatePassword,
					)
				);
			}
		}

		/**
		 * Uninstall plugin SSL keys.
		 *
		 * @return void
		 */
		public static function uninstall_keys() {
			delete_option( 'competitive_scheduling_openssl_keys' );
		}

		/**
		 * SSL public and private key pair generator.
		 *
		 * @param string      $type of openssl key that will be generated using the correct algorithm.
		 * @param string|null $password to encrypt the private key.
		 *
		 * @return array containing 'public', 'private' keys on success, false on failure.
		 */
		public static function generate_keys( $params = false ) {
			if ( $params ) {
				foreach ( $params as $var => $val ) {
					$$var = $val;
				}
			}

			$keys = false;

			if ( isset( $type ) ) {
				switch ( $type ) {
					case 'RSA':
						$config = array(
							'digest_alg'       => 'sha512',
							'private_key_bits' => 2048,
							'private_key_type' => OPENSSL_KEYTYPE_RSA,
						);

						if ( CS_DEBUG ) {
							$config['config'] = 'C:\xampp\apache\conf\openssl.cnf';
						}

						$res = openssl_pkey_new( $config );

						if ( ! empty( $password ) ) {
							openssl_pkey_export( $res, $privateKey, $password );
						} else {
							openssl_pkey_export( $res, $privateKey );
						}

						$privateKeyDetails = openssl_pkey_get_details( $res );
						$publicKey         = $privateKeyDetails['key'];

						return array(
							'public'  => $publicKey,
							'private' => $privateKey,
						);
					break;
				}
			}

			return $keys;
		}

		/**
		 * Generate the validation token.
		 *
		 * @param string|null $pubID public token identifier.
		 *
		 * @return array containing 'token' and 'pubID' on success, empty array on failure.
		 */
		public static function generate_token_validation( $params = false ) {
			if ( $params ) {
				foreach ( $params as $var => $val ) {
					$$var = $val;
				}
			}

			// Define variables to generate the JWT.
			$expiration = time() + CS_NOUNCE_SCHEDULES_EXPIRES;

			// Get the host's public key.
			$cs_openssl_keys = get_option( 'competitive_scheduling_openssl_keys' );

			if ( ! empty( $cs_openssl_keys['public'] ) ) {
				$publicKey = $cs_openssl_keys['public'];

				// Generate Token ID.
				if ( isset( $pubID ) ) {
					$tokenPubId = $pubID;
				} else {
					$tokenPubId = md5( uniqid( rand(), true ) );
				}

				// Generate the JWT token.
				$token = self::generate_jwt(
					array(
						'host'       => $_SERVER['SERVER_NAME'],
						'expiration' => $expiration,
						'pubID'      => $tokenPubId,
						'publicKey'  => $publicKey,
					)
				);

				return array(
					'token' => $token,
					'pubID' => $tokenPubId,
				);
			}

			return array();
		}

		/**
		 * Validate the validation token.
		 *
		 * @param string $token JWT generated previously.
		 *
		 * @return boolean | string containing 'pubID' on success, false on failure.
		 */
		public static function validate_token_validation( $params = false ) {
			if ( $params ) {
				foreach ( $params as $var => $val ) {
					$$var = $val;
				}
			}

			if ( isset( $token ) ) {
				// Checks if the token exists.
				$JWTToken = $token;

				if ( empty( $JWTToken ) ) {
					return false;
				}

				// Get the host's public key.
				$cs_openssl_keys = get_option( 'competitive_scheduling_openssl_keys' );

				echo 'validate_token_validation';

				if ( ! empty( $cs_openssl_keys['private'] || ! empty( $cs_openssl_keys['private-password'] ) ) ) {
					// Open private key and key password.
					$privateKey         = $cs_openssl_keys['private'];
					$privateKeyPassword = $cs_openssl_keys['private-password'];

					echo ' - entrou no if';

					// Check if the JWT is valid.
					$tokenPubId = self::validate_jwt(
						array(
							'token'              => $JWTToken,
							'privateKey'         => $privateKey,
							'privateKeyPassword' => $privateKeyPassword,
						)
					);

					return $tokenPubId;
				}
			}

			return false;
		}

		/**
		 * Generate JWT.
		 *
		 * @param string $host JWT access code.
		 * @param int    $expiration JWT Expiration.
		 * @param string $pubID public ID of the token for reference.
		 * @param string $publickey public key to sign the JWT.
		 *
		 * @return string
		 */
		private static function generate_jwt( $params = false ) {
			$cryptMaxCharsValue = 245; // There are char limitations on openssl_private_encrypt() and in the url below are explained how define this value based on openssl key format: https://www.php.net/manual/en/function.openssl-private-encrypt.php#119810

			if ( $params ) {
				foreach ( $params as $var => $val ) {
					$$var = $val;
				}
			}

			if ( isset( $host ) && isset( $expiration ) && isset( $pubID ) && isset( $publicKey ) ) {
				// Header
				$header = array(
					'alg' => 'RSA',
					'typ' => 'JWT',
				);

				$header = json_encode( $header );
				$header = base64_encode( $header );

				// Payload
				$payload = array(
					'iss' => $host, // The issuer of the token
					'exp' => $expiration, // This will define the expiration in NumericDate value. The expiration MUST be after the current date/time.
					'sub' => $pubID, // ID público do totken
				);

				$payload = json_encode( $payload );
				$payload = base64_encode( $payload );

				// Join header with payload to generate signature.
				$rawDataSource = $header . '.' . $payload;

				// Sign using RSA SSL.
				$resPublicKey = openssl_get_publickey( $publicKey );

				$partialData = '';
				$encodedData = '';
				$split       = str_split( $rawDataSource, $cryptMaxCharsValue );
				foreach ( $split as $part ) {
					openssl_public_encrypt( $part, $partialData, $resPublicKey );
					$encodedData .= ( strlen( $encodedData ) > 0 ? '.' : '' ) . base64_encode( $partialData );
				}

				$encodedData = base64_encode( $encodedData );

				$signature = $encodedData;

				// Finalize and return the JWT token.
				$JWTToken = $header . '.' . $payload . '.' . $signature;

				return $JWTToken;
			} else {
				return false;
			}
		}

		/**
		 * Validate JWT.
		 *
		 * @param string $token JWT verification token.
		 * @param string $privateKey private key to verify the token signature.
		 * @param string $privateKeyPassword private key password.
		 *
		 * @return boolean
		 */
		private static function validate_jwt( $params = false ) {
			if ( $params ) {
				foreach ( $params as $var => $val ) {
					$$var = $val;
				}
			}

			if ( isset( $token ) && isset( $privateKey ) && isset( $privateKeyPassword ) ) {
				// Break the token into header, payload and signature.
				$part = explode( '.', $token );

				if ( gettype( $part ) != 'array' ) {
					return false;
				}

				$header    = $part[0];
				$payload   = $part[1];
				$signature = $part[2];

				$encodedData = $signature;

				// Open private key with password.
				$resPrivateKey = openssl_get_privatekey( $privateKey, $privateKeyPassword );

				// Decode base64 to reaveal dots (Dots are used in JWT syntaxe)
				$encodedData = base64_decode( $encodedData );

				// Decrypt data in parts if necessary. Using dots as split separator.
				$rawEncodedData = $encodedData;

				$countCrypt         = 0;
				$partialDecodedData = '';
				$decodedData        = '';
				$split2             = explode( '.', $rawEncodedData );
				foreach ( $split2 as $part2 ) {
					$part2 = base64_decode( $part2 );

					openssl_private_decrypt( $part2, $partialDecodedData, $resPrivateKey );
					$decodedData .= $partialDecodedData;
				}

				// Validate JWT
				if ( $header . '.' . $payload === $decodedData ) {
					$payload = base64_decode( $payload );
					$payload = json_decode( $payload, true );

					// Check if the variables exist, otherwise it was formatted wrong and should not be accepted.
					if ( ! isset( $payload['exp'] ) || ! isset( $payload['sub'] ) ) {
						return false;
					}

					$expiration_ok = false;

					// If the token's expiration time is shorter than the time now, it is because this token has expired.
					if ( (int) $payload['exp'] > time() ) {
						$expiration_ok = true;
					}

					if ( $expiration_ok ) {
						// If everything is valid, returns the pubID of the token.
						return $payload['sub'];
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
}
