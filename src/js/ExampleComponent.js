/*
 * An example component that will be rendered at the bottom of the sidebar in
 * the Cart and Checkout blocks.
 */
export const ExampleComponent = ({ data }) => {
	return (
		<div className="eu-vat-for-woocommerce-example-component">
			Data passed to this component: {data['example-data']}
		</div>
	);
};
