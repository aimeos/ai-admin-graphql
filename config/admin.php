<?php

return [
	'ai' => [
		'write' => [
			'provider' => 'openai',
			'model' => 'gpt-5.6-sol',
		],
		'translate' => [
			'provider' => 'deepl',
		],
		'imagine' => [
			'provider' => 'openai',
			'model' => 'gpt-image-1.5',
		],
		'isolate' => [
			'provider' => 'removebg',
		],
	],
	'graphql' => [
		/** admin/graphql/domains
		 * List of domains available via the GraphQL API
		 *
		 * Queries and mutations are only available for the domains in this
		 * list. Remove domains you don't want to manage via GraphQL or add the
		 * domains of your own extensions. The class used for each domain is
		 * configured by "admin/graphql/<domain>/name" and users also need the
		 * permissions configured below "admin/graphql/resource".
		 *
		 * @param array Associative list of domain names as keys and values
		 * @since 2022.10
		 * @see admin/graphql/name
		 * @see admin/graphql/lists-domains
		 */
		'domains' => [
			'ai' => 'ai',
			'attribute' => 'attribute',
			'attribute/type' => 'attribute/type',
			'attribute/lists/type' => 'attribute/lists/type',
			'attribute/property/type' => 'attribute/property/type',
			'catalog' => 'catalog',
			'catalog/lists/type' => 'catalog/lists/type',
			'coupon' => 'coupon',
			'coupon/code' => 'coupon/code',
			'customer' => 'customer',
			'customer/lists/type' => 'customer/lists/type',
			'customer/property/type' => 'customer/property/type',
			'group' => 'group',
			'index' => 'index',
			'locale' => 'locale',
			'locale/currency' => 'locale/currency',
			'locale/language' => 'locale/language',
			'locale/site' => 'locale/site',
			'media' => 'media',
			'media/type' => 'media/type',
			'media/lists/type' => 'media/lists/type',
			'media/property/type' => 'media/property/type',
			'order' => 'order',
			'order/status' => 'order/status',
			'plugin' => 'plugin',
			'plugin/type' => 'plugin/type',
			'price' => 'price',
			'price/type' => 'price/type',
			'price/lists/type' => 'price/lists/type',
			'price/property/type' => 'price/property/type',
			'product' => 'product',
			'product/type' => 'product/type',
			'product/lists/type' => 'product/lists/type',
			'product/property/type' => 'product/property/type',
			'review' => 'review',
			'rule' => 'rule',
			'rule/type' => 'rule/type',
			'service' => 'service',
			'service/type' => 'service/type',
			'service/lists/type' => 'service/lists/type',
			'stock' => 'stock',
			'stock/type' => 'stock/type',
			'subscription' => 'subscription',
			'supplier' => 'supplier',
			'supplier/lists/type' => 'supplier/lists/type',
			'tag' => 'tag',
			'tag/type' => 'tag/type',
			'text' => 'text',
			'text/type' => 'text/type',
			'text/lists/type' => 'text/lists/type',
			'type' => 'type',
		],
		/** admin/graphql/lists-domains
		 * Domains of the referenced items available via list items
		 *
		 * Items like products or catalogs can reference other items via list
		 * items, e.g. texts, prices or media. GraphQL clients can only fetch
		 * and save referenced items of the domains in this list together with
		 * the parent item. Add the domains of your own extensions if their
		 * items should be available as references too.
		 *
		 * @param array Associative list of domain names as keys and values
		 * @since 2022.10
		 * @see admin/graphql/domains
		 */
		'lists-domains' => [
			'attribute' => 'attribute',
			'catalog' => 'catalog',
			'customer' => 'customer',
			'group' => 'group',
			'media' => 'media',
			'price' => 'price',
			'product' => 'product',
			'service' => 'service',
			'supplier' => 'supplier',
			'tag' => 'tag',
			'text' => 'text',
		]
	],
];
