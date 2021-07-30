/**
 * External dependencies
 */
import '@wordpress/notices';
import { Spinner } from '@woocommerce/components';

/**
 * Internal dependencies
 */
import SetupProductSync from '../steps/SetupProductSync';
import SetupPins from '../steps/SetupPins';
import AdvancedSettings from '../steps/AdvancedSettings';
import SaveSettingsButton from '../components/SaveSettingsButton';
import TransientNotices from '../components/TransientNotices';
import {
	useSettingsSelect,
	useBodyClasses,
	useCreateNotice,
} from '../helpers/effects';

import NavigationClassic from '../../../components/navigation-classic';

const SettingsApp = () => {
	const appSettings = useSettingsSelect();

	useBodyClasses();
	useCreateNotice()( wcSettings.pin4wc.error );

	return (
		<div className="woocommerce-layout">
			<div className="woocommerce-layout__main">
				<NavigationClassic />

				<TransientNotices />
				{ appSettings ? (
					<div className="woocommerce-setup-guide__container">
						<>
							<SetupProductSync view="settings" />
							<SetupPins view="settings" />
							<AdvancedSettings view="settings" />
							<SaveSettingsButton />
						</>
					</div>
				) : (
					<Spinner />
				) }
			</div>
		</div>
	);
};

export default SettingsApp;
