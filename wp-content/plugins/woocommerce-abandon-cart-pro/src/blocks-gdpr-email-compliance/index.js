/**
 * External dependencies
 */
import { SVG } from '@wordpress/components';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
// import { Edit, Save } from './edit';
import metadata from './block.json';
registerBlockType(metadata, {
	icon: {
		src: (
			<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 16">
				<g fill="none" fillRule="evenodd">
					<path
						stroke="currentColor"
						strokeWidth="1.5"
						d="M2 .75h16c.69 0 1.25.56 1.25 1.25v12c0 .69-.56 1.25-1.25 1.25H2c-.69 0-1.25-.56-1.25-1.25V2C.75 1.31 1.31.75 2 .75z"
					/>
					<path
						fill="currentColor"
						d="M7.667 7.667A2.34 2.34 0 0010 5.333 2.34 2.34 0 007.667 3a2.34 2.34 0 00-2.334 2.333 2.34 2.34 0 002.334 2.334zM11.556 3H17v3.889h-5.444V3zm2.722 2.916l1.944-1.36v-.779L14.278 5.14l-1.945-1.362v.778l1.945 1.361zm-5.834-.583a.78.78 0 00-.777-.777.78.78 0 00-.778.777c0 .428.35.778.778.778a.78.78 0 00.777-.778zm3.89 5.904c0-1.945-3.088-2.785-4.667-2.785-1.58 0-4.667.84-4.667 2.785v1.097h9.333v-1.097zM7.666 10c-1.012 0-2.163.389-2.738.778h5.475C9.821 10.38 8.678 10 7.667 10z"
					/>
				</g>
			</SVG>
		),
		foreground: '#874FB9',
	},
//	edit: Edit,
//	save: Save,
});
