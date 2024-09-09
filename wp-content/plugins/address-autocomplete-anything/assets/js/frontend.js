function wps_aa() {

    for ( address_group of wps_aa_vars.instances ) {
        if ( address_group.init ) {
            // If the init selector is found on this page, then let's get this added!
            if ( ! address_group.delay ) {
				wps_aa_init_autocomplete( address_group );
			} else if ( address_group.delay ) {
				setTimeout( wps_aa_init_autocomplete, address_group.delay * 1000, address_group );
            }
        }
    }

}

function wps_aa_init_autocomplete( address_group ) {
    const init_selector = document.querySelector( address_group.init );
	if ( ! init_selector ) {
		console.log( 'Could not find address autocomplete initial selector: ' + address_group.init );
		return;
	}
    var options = {
        fields: [ "address_components", "geometry", "name" ],
    };
    // Optionally set the allowed countries if defined in settings
    if ( address_group.allowed_countries ) {
        options.componentRestrictions = { country: address_group.allowed_countries }
    }
    const autocomplete = new google.maps.places.Autocomplete( init_selector, options );
    autocomplete.addListener( "place_changed", () => {

        let values = {};
        let replacements = [];
        let populations = [];
		let final_data = [];

        // Get the place data
        const place = autocomplete.getPlace();
		console.log( "Address found:", place );

        // Go through place address components and build all the possible replacement values
        for ( const place_components of place.address_components ) {
            // Set this component type value
            values[ place_components.types[0] ] = place_components;
        }

		if ( place.hasOwnProperty( 'name' ) ) {
			values['name'] = { long_name: place.name, short_name: place.name };
		}

        // Attempts at country specific issues
		/*
        if ( values.country.short_name == 'GB' ) { // UK has some oddities to deal with
            if ( values['postal_town'] !== undefined ) {
                values['locality'] = values['postal_town'];
                delete values['administrative_area_level_1'];
            }
        }

        if ( values.country.short_name == 'IE' ) { // Ireland has some oddities to deal with
            if ( values['sublocality_level_1'] !== undefined ) {
                values['locality'] = values['sublocality_level_1'];
            }
        }
		*/

        for ( var k in values ) {

            let short_replacement = {};
            short_replacement.search = '{' + k + ':short_name}';
            if ( values[k].short_name ) {
                short_replacement.replace = values[k].short_name;
            } else {
                short_replacement.replace = '';
            }
            replacements.push( short_replacement );

            let long_replacement = {};
            long_replacement.search = '{' + k + ':long_name}';
            long_replacement.replace = values[k].long_name;
            replacements.push( long_replacement );

        }

        // Special Lat/Lng replacements
        let lat_replacement = {};
        lat_replacement.search = '{lat}';
        lat_replacement.replace = place.geometry.location.lat();
        replacements.push( lat_replacement );

        let lng_replacement = {};
        lng_replacement.search = '{lng}';
        lng_replacement.replace = place.geometry.location.lng();
        replacements.push( lng_replacement );

        // Address 1 replacement which needs to be done based on country and it's respective format
        let address1_replacement_short = {};
        let address1_replacement_long = {};
        address1_replacement_short.search = '{address1:short_name}';
        address1_replacement_long.search = '{address1:long_name}';
        if ( wps_aa_address1_format( values.country.short_name ) == 'standard' ) {
			address1_replacement_short.replace = '';
			address1_replacement_long.replace = '';
			if ( values.street_number && values.street_number.short_name ) {
				address1_replacement_short.replace = values.street_number.short_name;
				address1_replacement_long.replace = values.street_number.long_name;
			}
			if ( values.route && values.route.short_name ) {
				if ( address1_replacement_short.replace != '' ) {
					address1_replacement_short.replace += ' ';
				}
				address1_replacement_short.replace += values.route.short_name;
				if ( address1_replacement_long.replace != '' ) {
					address1_replacement_long.replace += ' ';
				}
	            address1_replacement_long.replace += values.route.long_name;
			}
        } else {
			address1_replacement_short.replace = '';
			address1_replacement_long.replace = '';
			if ( values.route && values.route.short_name ) {
				address1_replacement_short.replace = values.route.short_name;
				address1_replacement_long.replace = values.route.long_name;
			}
			if ( values.street_number && values.street_number.short_name ) {
				if ( address1_replacement_short.replace != '' ) {
					address1_replacement_short.replace += ' ';
				}
				address1_replacement_short.replace += values.street_number.short_name;
				if ( address1_replacement_long.replace != '' ) {
					address1_replacement_long.replace += ' ';
				}
	            address1_replacement_long.replace += values.street_number.long_name;
			}
        }
        replacements.push( address1_replacement_short );
        replacements.push( address1_replacement_long );

        //  Go through all available fields and to the search/replace on them
        for ( const key in address_group.fields ) {

            let selector = address_group.fields[key].selector;
            let data = address_group.fields[key].data.toString();
            let result = data;
            let replace;
            let attributes = wps_aa_parse_atts( data );

            // Strip the attributes now for easier replace
            let attribute_strings = result.match(/[\w-]+=".*?"/g);
            if ( attribute_strings ) {
                attribute_strings.forEach( function( attribute ) {
                    result = result.replace( attribute, '' );
                } );
            }
            result = result.replace( /\s/g, '' ); // Should no longer be any spaces

            // Loop through each replacement and run it on this data
            for ( replacement of replacements ) {

                // Set the replacement string by checking the before/after attributes
                replace = replacement.replace;
                if ( Object.keys( attributes ).length > 0 ) {
                    attributes.forEach( function( attribute ) {
                        // Loop through attributes and see if key matches this search item
                        if ( replacement.search == attribute.key ) {
                            if ( attribute.hasOwnProperty( 'before' ) ) {
                                replace = attribute.before + replace;
                            }
                            if ( attribute.hasOwnProperty( 'after' ) ) {
                                replace = replace + attribute.after;
                            }
                        }
                    } );
                }

                result = result.replace( replacement.search, replace );

            }

            // Replace all leftover placeholders with empty string
            result = result.replace( /{.*}/, '' );

            wps_aa_change_value( selector, result );

			final_data.push( { selector: selector, result: result } );

        }

		const wps_aa_event = new CustomEvent( 'wps_aa', { detail: { data: final_data, init: address_group.init } } );
		document.dispatchEvent( wps_aa_event );

    });

}

function wps_aa_parse_atts(inputString) {
    const regex = /{([^{}]+)}/g;
    const matches = inputString.match(regex);
    const attributes = [];

    if (matches) {
        for (const match of matches) {
            let attributeString = match.slice(1, -1);
            const attributePairs = attributeString.match(/[\w-]+=".*?"/g);

            if (attributePairs) {

                attributePairs.forEach(function(attribute) {
                    attributeString = attributeString.replace(attribute, '');
                });
                attributeString = attributeString.replace(/\s/g, '');
                const attributeObj = {
                    key: '{' + attributeString + '}'
                };

                for (const pair of attributePairs) {
                    const [attrKey, attrValue] = pair.split('=');
                    const trimmedKey = attrKey.trim();
                    const trimmedValue = attrValue.slice(1, -1);
                    attributeObj[trimmedKey] = trimmedValue;
                }

                attributes.push(attributeObj);
            } else {
                // No attributes found, add the entire string as the key with empty attribute values
                attributes.push({
                    key: match
                });
            }
        }
    }

    return attributes;
}

function wps_aa_change_value( selector, data ) {
    where_to_populate = document.querySelector( selector );
    if ( where_to_populate ) {
        where_to_populate.value = data;
        where_to_populate.dispatchEvent( new Event( 'change' ) );
        // If the element changing has this select2 specific class, we want assume jQuery is already enabled and available to use this change trigger
        if ( where_to_populate.classList.contains( 'select2-hidden-accessible' ) ) {
            jQuery( selector ).trigger( 'change' );
        }
    } else {
		console.error( "Cannot find selector to attach address auto complete data", selector, data );
	}
}

function wps_aa_address1_format( country ) {
    // These countries have the street number after the street name
    const reverse_countries = [ 'DE', 'AT', 'MX', 'CH', 'NL' ];
    if ( reverse_countries.includes( country ) ) {
        return 'reverse';
    }
    return 'standard';
}
