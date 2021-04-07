/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Card,
	CardBody
 } from '@wordpress/components';
import { OPTIONS_STORE_NAME } from '@woocommerce/data';

/**
  * Internal dependencies
  */
import StepHeader from '../StepHeader';
import StepOverview from '../StepOverview';
import StepStatus from '../StepStatus';

const VerifyDomain = ({ goToNextStep, pin4wc, createNotice }) => {
	const [ status, setStatus ] = useState( 'idle' );

	useEffect(() => {
		if ( pin4wc.verfication_code && 'success' !== status ) {
			setDebugEmails( 'success' );
		}
	}, [pin4wc.verfication_code])

	const buttonLabels = {
		idle: __( 'Start Verification', 'pinterest-for-woocommerce' ),
		pending: __( 'Verifying Domain', 'pinterest-for-woocommerce' ),
		error: __( 'Try Again', 'pinterest-for-woocommerce' ),
		success: __( 'Continue', 'pinterest-for-woocommerce' )
	}

	const handleVerifyDomain = () => {
		setStatus( 'pending' );

		apiFetch( {
			path: pin4wcSetupGuide.apiRoute + '/domain_verification',
			method: 'POST',
		} ).then( () => {
			setStatus( 'success' );
		} ).catch( () => {
			setStatus( 'error' );

			createNotice(
				'error',
				__(
					'Couldn’t verify your domain.',
					'pinterest-for-woocommerce'
				)
			);
		} );
	}

	return (
		<div className="woocommerce-setup-guide__verify-domain">
			<StepHeader
				title={ __( 'Verify your domain' ) }
				subtitle={ __( 'Step Two' ) }
			/>

			<div className="woocommerce-setup-guide__step-columns">
				<div className="woocommerce-setup-guide__step-column">
					<StepOverview
						title={ __( 'Verify your domain' ) }
						description={ __( 'Claim your website yo get access to analytics for the Pins you publish from your site, the analytics on Pins that other people create from your site and let people know where they can find more of you content.' ) }
						link='#'
					/>
				</div>
				<div className="woocommerce-setup-guide__step-column">
					<Card>
						<CardBody size="large">
							<StepStatus
								label='https://www.pinterest.com'
								status={ status }
							/>
						</CardBody>
					</Card>

					<Button
						isPrimary
						className="woocommerce-setup-guide__footer-button"
						disabled={ 'pending' === status }
						onClick={ 'success' === status ? goToNextStep : handleVerifyDomain }
					>
						{ buttonLabels[ status ] }
					</Button>
				</div>
			</div>
		</div>
	);
}

export default compose(
	withSelect( select => {
		const { getOption } = select( OPTIONS_STORE_NAME );

		return {
			pin4wc: getOption( pin4wcSetupGuide.optionsName ) || [],
		}
	}),
	withDispatch( dispatch => {
		const { createNotice } = dispatch( 'core/notices' );

		return {
			createNotice,
		};
	})
)(VerifyDomain);
