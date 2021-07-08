/**
 * External dependencies
 */
import { sprintf, __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Card,
	CardBody,
	CardFooter,
	Flex,
	FlexItem,
	FlexBlock,
	Modal,
	__experimentalText as Text,
} from '@wordpress/components';
import { Spinner } from '@woocommerce/components';

/**
 * Internal dependencies
 */
import StepHeader from '../components/StepHeader';
import StepOverview from '../components/StepOverview';
import { useSettingsSelect, useCreateNotice } from '../helpers/effects';

const SetupAccount = ({ goToNextStep, view, isConnected, setIsConnected }) => {
	const [isConfirmationModalOpen, setIsConfirmationModalOpen] =
		useState(false);
	const appSettings = useSettingsSelect();

	const createNotice = useCreateNotice();

	const openConfirmationModal = () => {
		setIsConfirmationModalOpen(true);
	};

	const closeConfirmationModal = () => {
		setIsConfirmationModalOpen(false);
	};

	const renderConfirmationModal = () => {
		return (
			<Modal
				title={<>{__('Are you sure?', 'pinterest-for-woocommerce')}</>}
				onRequestClose={closeConfirmationModal}
				className="woocommerce-setup-guide__step-modal"
			>
				<div className="woocommerce-setup-guide__step-modal__wrapper">
					<p>
						{__(
							'Are you sure you want to disconnect this account?',
							'pinterest-for-woocommerce'
						)}
					</p>
					<div className="woocommerce-setup-guide__step-modal__buttons">
						<Button
							isDestructive
							isSecondary
							onClick={handleDisconnectAccount}
						>
							{__("Yes, I'm sure", 'pinterest-for-woocommerce')}
						</Button>
						<Button isTertiary onClick={closeConfirmationModal}>
							{__('Cancel', 'pinterest-for-woocommerce')}
						</Button>
					</div>
				</div>
			</Modal>
		);
	};

	const handleDisconnectAccount = async () => {
		closeConfirmationModal();

		const result = await apiFetch({
			path: wcSettings.pin4wc.apiRoute + '/auth_disconnect',
			method: 'POST',
		});

		if (!result.disconnected) {
			createNotice(
				'error',
				__(
					'There was a problem while trying to disconnect.',
					'pinterest-for-woocommerce'
				)
			);
		} else {
			setIsConnected(false);
		}
	};

	return (
		<div className="woocommerce-setup-guide__setup-account">
			{view === 'wizard' && (
				<StepHeader
					title={__(
						'Set up your account',
						'pinterest-for-woocommerce'
					)}
					subtitle={__('Step One', 'pinterest-for-woocommerce')}
					description={__(
						'Use description text to help users understand what accounts they need to connect, and why they need to connect it.',
						'pinterest-for-woocommerce'
					)}
				/>
			)}

			<div className="woocommerce-setup-guide__step-columns">
				<div className="woocommerce-setup-guide__step-column">
					<StepOverview
						title={__(
							'Pinterest Account',
							'pinterest-for-woocommerce'
						)}
						description={__(
							'Use description text to help users understand more',
							'pinterest-for-woocommerce'
						)}
					/>
				</div>
				<div className="woocommerce-setup-guide__step-column">
					<Card>
						<CardBody size="large">
							{isConnected === true ? (
								<Flex>
									<FlexBlock className="is-connected">
										<Text variant="subtitle">
											{__(
												'Pinterest Account',
												'pinterest-for-woocommerce'
											)}
										</Text>
										{appSettings?.account_data?.id && (
											<Text variant="body">
												{sprintf(
													'%1$s: %2$s - %3$s',
													__(
														'Account',
														'pinterest-for-woocommerce'
													),
													appSettings.account_data
														.username,
													appSettings.account_data.id
												)}
											</Text>
										)}
										<Button
											isLink
											isDestructive
											onClick={openConfirmationModal}
										>
											{__(
												'Disconnect Pinterest Account',
												'pinterest-for-woocommerce'
											)}
										</Button>
									</FlexBlock>
								</Flex>
							) : isConnected === false ? (
								<Flex>
									<FlexBlock>
										<Text variant="subtitle">
											{__(
												'Connect your Pinterest Account',
												'pinterest-for-woocommerce'
											)}
										</Text>
									</FlexBlock>
									<FlexItem>
										<Button
											isSecondary
											href={decodeEntities(
												wcSettings.pin4wc
													.serviceLoginUrl
											)}
										>
											{__(
												'Connect',
												'pinterest-for-woocommerce'
											)}
										</Button>
									</FlexItem>
								</Flex>
							) : (
								<Spinner />
							)}
						</CardBody>

						{isConnected === false && (
							<CardFooter>
								<Button
									isLink
									href={
										wcSettings.pin4wc.pinterestLinks
											.newAccount
									}
									target="_blank"
								>
									{__(
										'Or, create a new Pinterest account',
										'pinterest-for-woocommerce'
									)}
								</Button>
							</CardFooter>
						)}

						{isConfirmationModalOpen && renderConfirmationModal()}
					</Card>

					{view === 'wizard' && isConnected === true && (
						<div className="woocommerce-setup-guide__footer-button">
							<Button isPrimary onClick={goToNextStep}>
								{__('Continue', 'pinterest-for-woocommerce')}
							</Button>
						</div>
					)}
				</div>
			</div>
		</div>
	);
};

export default SetupAccount;
