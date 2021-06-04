/**
 * External dependencies
 */
import '@wordpress/notices';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { createElement, useState } from '@wordpress/element';
import { Spinner, Stepper } from '@woocommerce/components';
import {
	getHistory,
	getQuery,
	updateQueryString,
} from '@woocommerce/navigation';

/**
 * Internal dependencies
 */
import SetupAccount from '../steps/SetupAccount';
import ClaimWebsite from '../steps/ClaimWebsite';
import SetupTracking from '../steps/SetupTracking';
import SetupPins from '../steps/SetupPins';
import TransientNotices from '../components/TransientNotices';
import { useBodyClasses, useCreateNotice } from '../helpers/effects';
import { SETTINGS_STORE_NAME } from '../data';

const WizardApp = () => {
	const [ currentStep, setCurrentStep ] = useState();

	const appSettings = useSelect( ( select ) =>
		select( SETTINGS_STORE_NAME ).getSettings()
	);

	const { updateSettings } = useDispatch( SETTINGS_STORE_NAME );
	const { createNotice } = useDispatch( 'core/notices' );

	const childComponentProps = {
		appSettings,
		setAppSettings: ( data ) => updateSettings( data, true ),
		createNotice,
	};

	useBodyClasses( 'wizard' );
	useCreateNotice( wcSettings.pin4wc.error );

	const steps = [
		{
			key: 'setup-account',
			container: SetupAccount,
			label: __( 'Set up your account', 'pinterest-for-woocommerce' ),
		},
		{
			key: 'claim-website',
			container: ClaimWebsite,
			label: __( 'Claim your website', 'pinterest-for-woocommerce' ),
		},
		{
			key: 'setup-tracking',
			container: SetupTracking,
			label: __( 'Set up tracking', 'pinterest-for-woocommerce' ),
		},
		{
			key: 'setup-pins',
			container: SetupPins,
			label: __( 'Set up pins', 'pinterest-for-woocommerce' ),
		},
	];

	const getSteps = () => {
		return steps.map( ( step, index ) => {
			const container = createElement( step.container, {
				...childComponentProps,
				query: getQuery(),
				step,
				goToNextStep: () => goToNextStep( step ),
				view: 'wizard',
			} );

			step.content = (
				<div
					className={ `woocommerce-setup-guide__container ${ step.key }` }
				>
					{ container }
				</div>
			);

			const previousStep = steps[ index - 1 ];

			if ( ! previousStep || previousStep.isComplete ) {
				step.onClick = ( key ) => updateQueryString( { step: key } );
			}

			return step;
		} );
	};

	const getCurrentStep = () => {
		const query = getQuery();
		const step = steps.find( ( s ) => s.key === query.step );

		if ( ! step ) {
			return steps[ 0 ].key;
		}

		return step.key;
	};

	const goToNextStep = ( step ) => {
		const currentStepIndex = steps.findIndex( ( s ) => s.key === step.key );

		const nextStep = steps[ currentStepIndex + 1 ];

		if ( typeof nextStep === 'undefined' ) {
			return;
		}

		return updateQueryString( { step: nextStep.key } );
	};

	getHistory().listen( () => {
		setCurrentStep( getCurrentStep() );
	} );

	if ( ! currentStep ) {
		setCurrentStep( getCurrentStep() );

		return <Spinner />;
	}

	return (
		<div className="woocommerce-layout">
			<div className="woocommerce-layout__main woocommerce-setup-guide__main">
				<TransientNotices />
				{ appSettings ? (
					<Stepper currentStep={ currentStep } steps={ getSteps() } />
				) : (
					<Spinner />
				) }
			</div>
		</div>
	);
};

export default WizardApp;
