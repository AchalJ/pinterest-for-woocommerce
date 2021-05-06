/**
 * External dependencies
 */
import '@wordpress/notices';
import { useSelect } from '@wordpress/data';
import { OPTIONS_STORE_NAME } from '@woocommerce/data';

/**
 * Internal dependencies
 */
import SetupAccount from '../steps/SetupAccount';
import VerifyDomain from '../steps/VerifyDomain';
import ConfigureSettings from '../steps/ConfigureSettings';
import TransientNotices from '../components/TransientNotices';
import { useBodyClasses, useCreateNotice } from '../helpers/effects';

const SettingsApp = () => {
	const pin4wc = useSelect( ( select ) =>
		select( OPTIONS_STORE_NAME ).getOption( wcSettings.pin4wc.optionsName )
	);

	useBodyClasses();
	useCreateNotice( wcSettings.pin4wc.error );

	const isConnected = () => {
		return undefined === pin4wc
			? undefined
			: !! pin4wc?.token?.access_token;
	};

	const isDomainVerified = () => {
		return undefined === pin4wc
			? undefined
			: undefined === pin4wc?.account_data?.verified_domains
			? false
			: pin4wc?.account_data?.verified_domains.includes(
					wcSettings.pin4wc.domainToVerify
			  );
	};

	return (
		<div className="woocommerce-layout">
			<div className="woocommerce-layout__main">
				<TransientNotices />
				<div className="woocommerce-setup-guide__container">
					<SetupAccount view="settings" />
					{ isConnected() && <VerifyDomain view="settings" /> }
					{ isConnected() && isDomainVerified() && (
						<ConfigureSettings view="settings" />
					) }
				</div>
			</div>
		</div>
	);
};

export default SettingsApp;
