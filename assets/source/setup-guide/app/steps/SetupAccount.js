/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	Button,
	Card,
	CardBody,
	CardFooter,
	Flex,
	FlexItem,
	FlexBlock,
	__experimentalText as Text
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import StepHeader from '../components/StepHeader';
import StepOverview from '../components/StepOverview';

const SetupAccount = ({ view }) => {
	return (
		<div className="woocommerce-setup-guide__setup-account">
			{ 'wizard' === view &&
				<StepHeader
					title={ __( 'Set up your account', 'pinterest-for-woocommerce' ) }
					subtitle={ __( 'Step One', 'pinterest-for-woocommerce' ) }
					description={ __( 'Use description text to help users understand what accounts they need to connect, and why they need to connect it.', 'pinterest-for-woocommerce' ) }
				/>
			}

			<div className="woocommerce-setup-guide__step-columns">
				<div className="woocommerce-setup-guide__step-column">
					<StepOverview
						title={ __( 'Pinterest Account', 'pinterest-for-woocommerce' ) }
						description={ __( 'Use description text to help users understand more', 'pinterest-for-woocommerce' ) }
					/>
				</div>
				<div className="woocommerce-setup-guide__step-column">
					<Card>
						<CardBody size="large">
							<Flex>
								<FlexBlock>
									<Text variant="subtitle">{ __( 'Connect your Pinterest Account', 'pinterest-for-woocommerce' ) }</Text>
								</FlexBlock>
								<FlexItem>
									<Button isSecondary href={ pin4wcSetupGuide.serviceLoginUrl }>{ __( 'Connect', 'pinterest-for-woocommerce' ) }</Button>
								</FlexItem>
							</Flex>
						</CardBody>

						<CardFooter>
							<Button isLink href={ pin4wcSetupGuide.pinterestLinks.newAccount } target="_blank">{ __( 'Or, create a new Pinterest account', 'pinterest-for-woocommerce' ) }</Button>
						</CardFooter>
					</Card>
				</div>
			</div>
		</div>
	);
}

export default SetupAccount;
