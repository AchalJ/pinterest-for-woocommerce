/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { Spinner } from '@woocommerce/components';
import { addQueryArgs } from '@wordpress/url';
import { useState } from '@wordpress/element';
import {
	Button,
	CardBody,
	Flex,
	FlexBlock,
	FlexItem,
	SelectControl,
	__experimentalText as Text, // eslint-disable-line @wordpress/no-unsafe-wp-apis --- _experimentalText unlikely to change/disappear and also used by WC Core
} from '@wordpress/components';

const BusinessAccountSelection = ( {
	businessAccounts,
	setAttemptedCreation,
} ) => {
	const [ targetBusinessId, setTargetBusinessId ] = useState();

	const handleConnectToBusiness = () => {
		const newURL = addQueryArgs(
			decodeEntities(
				wcSettings.pinterest_for_woocommerce.switchBusinessAccountUrl
			),
			{ business_id: targetBusinessId }
		);

		window.location = new URL( newURL );
	};

	const handleAttemptedCreation = () => {
		setAttemptedCreation( true );
	};

	return undefined !== businessAccounts ? (
		<CardBody size="large" className="business-connection">
			{ businessAccounts.length > 0 ? (
				<>
					<Text variant="subtitle">
						{ __(
							'Select a business account',
							'pinterest-for-woocommerce'
						) }
					</Text>
					<Flex>
						<FlexBlock>
							<SelectControl
								options={ businessAccounts }
								onChange={ ( businessId ) =>
									setTargetBusinessId( businessId )
								}
							/>
						</FlexBlock>
						<FlexItem>
							<Button
								isSecondary
								onClick={ handleConnectToBusiness }
							>
								{ __( 'Connect', 'pinterest-for-woocommerce' ) }
							</Button>
						</FlexItem>
					</Flex>
				</>
			) : (
				<Flex>
					<FlexBlock>
						<Text variant="subtitle">
							{ __(
								'No business account detected',
								'pinterest-for-woocommerce'
							) }
						</Text>
						<Text variant="body">
							{ __(
								'A Pinterest business account is required to connect Pinterest with your WooCommerce store.',
								'pinterest-for-woocommerce'
							) }
						</Text>
					</FlexBlock>
					<FlexItem>
						<Button
							isSecondary
							href={ decodeEntities(
								wcSettings.pinterest_for_woocommerce
									.createBusinessAccountUrl
							) }
							onClick={ handleAttemptedCreation }
							target="_blank"
						>
							{ __(
								'Create business account',
								'pinterest-for-woocommerce'
							) }
						</Button>
					</FlexItem>
				</Flex>
			) }
		</CardBody>
	) : (
		<CardBody size="large">
			<Spinner />
		</CardBody>
	);
};

export default BusinessAccountSelection;
