/**
 * External dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { CheckboxControl } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';

const { optInDefaultText } = getSetting('wcap_sms_consent_data', '');

const WcapBlock = ({ children, checkoutExtensionData }) => {
	const [checked, setChecked] = useState(true);
	const { setExtensionData } = checkoutExtensionData;

	useEffect(() => {
		setExtensionData('wcap_sms_consent', 'optin', checked);
		var newValue = '';
		if ( checked ) {
			newValue = true;
		} else {
			newValue = false;
		}
		updateLocalStorage( 'wcap_sms_consent', newValue );
		
	}, [checked, setExtensionData]);

	const updateLocalStorage = (key, newValue) => {
		const oldValue = localStorage.getItem(key);
		localStorage.setItem(key, newValue);
	  
		const storageEvent = new StorageEvent('storage', {
		  key: key,
		  oldValue: oldValue,
		  newValue: newValue,
		  url: window.location.href,
		  storageArea: localStorage
		});
	  
		window.dispatchEvent(storageEvent);
	}
	
	return (
		<>
			<CheckboxControl
				id="wcap_sms_consent"
				checked={checked}
				onChange={setChecked}
			>
				{children || optInDefaultText}
			</CheckboxControl>

		</>
	);
};

export default WcapBlock;
