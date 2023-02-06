<?php

return [
	'attribute' => [
		/** admin/graphql/resource/attribute/groups
		 * List of user groups that are allowed to manage attribute items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'lists' => [
			/** admin/graphql/resource/attribute/lists/groups
			 * List of user groups that are allowed to manage attribute lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/attribute/lists/type/groups
				 * List of user groups that are allowed to manage attribute lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'property' => [
			/** admin/graphql/resource/attribute/property/groups
			 * List of user groups that are allowed to manage attribute property items
			 *
			 * @param array List of user group names
			 * @since 2018.07
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/attribute/property/type/groups
				 * List of user groups that are allowed to manage attribute property type items
				 *
				 * @param array List of user group names
				 * @since 2018.07
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/attribute/type/groups
			 * List of user groups that are allowed to manage attribute type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
	'catalog' => [
		/** admin/graphql/resource/catalog/groups
		 * List of user groups that are allowed to manage catalog items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'lists' => [
			/** admin/graphql/resource/catalog/lists/groups
			 * List of user groups that are allowed to manage catalog lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/catalog/lists/type/groups
				 * List of user groups that are allowed to manage catalog lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
	],
	'coupon' => [
		/** admin/graphql/resource/coupon/groups
		 * List of user groups that are allowed to manage coupon items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'code' => [
			/** admin/graphql/resource/coupon/code/groups
			 * List of user groups that are allowed to manage coupon code items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'config' => [
			/** admin/graphql/resource/coupon/config/groups
			 * List of user groups that are allowed to fetch available coupon configuration
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
	'customer' => [
		/** admin/graphql/resource/customer/groups
		 * List of user groups that are allowed to manage customer items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'address' => [
			/** admin/graphql/resource/customer/address/groups
			 * List of user groups that are allowed to manage customer address items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'group' => [
			/** admin/graphql/resource/customer/group/groups
			 * List of user groups that are allowed to manage customer group items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'lists' => [
			/** admin/graphql/resource/customer/lists/groups
			 * List of user groups that are allowed to manage customer lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/customer/lists/type/groups
				 * List of user groups that are allowed to manage customer lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'property' => [
			/** admin/graphql/resource/customer/property/groups
			 * List of user groups that are allowed to manage customer property items
			 *
			 * @param array List of user group names
			 * @since 2018.07
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/customer/property/type/groups
				 * List of user groups that are allowed to manage customer property type items
				 *
				 * @param array List of user group names
				 * @since 2018.07
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
	],
	'index' => [
		/** admin/graphql/resource/index/groups
		 * List of user groups that are allowed to manage index items
		 *
		 * @param array List of user group names
		 * @since 2020.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'attribute' => [
			/** admin/graphql/resource/index/attribute/groups
			 * List of user groups that are allowed to manage index attribute items
			 *
			 * @param array List of user group names
			 * @since 2020.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'attribute' => [
			/** admin/graphql/resource/index/attribute/groups
			 * List of user groups that are allowed to manage index attribute items
			 *
			 * @param array List of user group names
			 * @since 2020.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'catalog' => [
			/** admin/graphql/resource/index/catalog/groups
			 * List of user groups that are allowed to manage index catalog items
			 *
			 * @param array List of user group names
			 * @since 2020.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'price' => [
			/** admin/graphql/resource/index/price/groups
			 * List of user groups that are allowed to manage index price items
			 *
			 * @param array List of user group names
			 * @since 2020.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'supplier' => [
			/** admin/graphql/resource/index/supplier/groups
			 * List of user groups that are allowed to manage index supplier items
			 *
			 * @param array List of user group names
			 * @since 2020.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'text' => [
			/** admin/graphql/resource/index/text/groups
			 * List of user groups that are allowed to manage index text items
			 *
			 * @param array List of user group names
			 * @since 2020.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
	'locale' => [
		/** admin/graphql/resource/locale/groups
		 * List of user groups that are allowed to manage locale items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'super'],
		'site' => [
			/** admin/graphql/resource/locale/site/groups
			 * List of user groups that are allowed to manage locale site items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'super'],
		],
		'language' => [
			/** admin/graphql/resource/locale/language/groups
			 * List of user groups that are allowed to manage locale language items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'super'],
		],
		'currency' => [
			/** admin/graphql/resource/locale/currency/groups
			 * List of user groups that are allowed to manage locale currency items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'super'],
		],
	],
	'media' => [
		/** admin/graphql/resource/media/groups
		 * List of user groups that are allowed to manage media items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'lists' => [
			/** admin/graphql/resource/media/lists/groups
			 * List of user groups that are allowed to manage media lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/media/lists/type/groups
				 * List of user groups that are allowed to manage media lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/media/type/groups
			 * List of user groups that are allowed to manage media type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'property' => [
			/** admin/graphql/resource/media/property/groups
			 * List of user groups that are allowed to manage media property items
			 *
			 * @param array List of user group names
			 * @since 2018.07
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/media/property/type/groups
				 * List of user groups that are allowed to manage media property type items
				 *
				 * @param array List of user group names
				 * @since 2018.07
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
	],
	'order' => [
		/** admin/graphql/resource/order/groups
		 * List of user groups that are allowed to manage order items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'address' => [
			/** admin/graphql/resource/order/address/groups
			 * List of user groups that are allowed to manage order address items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'coupon' => [
			/** admin/graphql/resource/order/coupon/groups
			 * List of user groups that are allowed to manage order coupon items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'product' => [
			/** admin/graphql/resource/order/product/groups
			 * List of user groups that are allowed to manage order product items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'attribute' => [
				/** admin/graphql/resource/order/product/attribute/groups
				 * List of user groups that are allowed to manage order product attribute items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'service' => [
			/** admin/graphql/resource/order/service/groups
			 * List of user groups that are allowed to manage order service items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'attribute' => [
				/** admin/graphql/resource/order/service/attribute/groups
				 * List of user groups that are allowed to manage order service attribute items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'status' => [
			/** admin/graphql/resource/order/status/groups
			 * List of user groups that are allowed to manage order status items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
	'plugin' => [
		/** admin/graphql/resource/plugin/groups
		 * List of user groups that are allowed to manage plugin items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'super'],
		'config' => [
			/** admin/graphql/resource/plugin/config/groups
			 * List of user groups that are allowed to fetch available plugin configuration
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'super'],
		],
		'type' => [
			/** admin/graphql/resource/plugin/type/groups
			 * List of user groups that are allowed to manage plugin type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'super'],
		],
	],
	'price' => [
		/** admin/graphql/resource/price/groups
		 * List of user groups that are allowed to manage price items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'lists' => [
			/** admin/graphql/resource/price/lists/groups
			 * List of user groups that are allowed to manage price lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/price/lists/type/groups
				 * List of user groups that are allowed to manage price lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'property' => [
			/** admin/graphql/resource/price/property/groups
			 * List of user groups that are allowed to manage price property items
			 *
			 * @param array List of user group names
			 * @since 2019.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/price/property/type/groups
				 * List of user groups that are allowed to manage price property type items
				 *
				 * @param array List of user group names
				 * @since 2019.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/price/type/groups
			 * List of user groups that are allowed to manage price type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
	'product' => [
		/** admin/graphql/resource/product/groups
		 * List of user groups that are allowed to manage product items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'lists' => [
			/** admin/graphql/resource/product/lists/groups
			 * List of user groups that are allowed to manage product lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/product/lists/type/groups
				 * List of user groups that are allowed to manage product lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'property' => [
			/** admin/graphql/resource/product/property/groups
			 * List of user groups that are allowed to manage product property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/product/property/type/groups
				 * List of user groups that are allowed to manage product property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/product/type/groups
			 * List of user groups that are allowed to manage product type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
	'rule' => [
		/** admin/graphql/resource/rule/groups
		 * List of user groups that are allowed to manage rule items
		 *
		 * @param array List of user group names
		 * @since 2021.04
		 */
		'groups' => ['admin', 'editor', 'super'],
		'config' => [
			/** admin/graphql/resource/rule/config/groups
			 * List of user groups that are allowed to fetch available rule configuration
			 *
			 * @param array List of user group names
			 * @since 2021.04
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'type' => [
			/** admin/graphql/resource/rule/type/groups
			 * List of user groups that are allowed to manage rule type items
			 *
			 * @param array List of user group names
			 * @since 2021.04
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
	'service' => [
		/** admin/graphql/resource/service/groups
		 * List of user groups that are allowed to manage service items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'super'],
		'config' => [
			/** admin/graphql/resource/service/config/groups
			 * List of user groups that are allowed to fetch available service configuration
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'super'],
		],
		'lists' => [
			/** admin/graphql/resource/service/lists/groups
			 * List of user groups that are allowed to manage service lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'super'],
			'type' => [
				/** admin/graphql/resource/service/lists/type/groups
				 * List of user groups that are allowed to manage service lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/service/type/groups
			 * List of user groups that are allowed to manage service type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'super'],
		],
	],
	'stock' => [
		/** admin/graphql/resource/stock/groups
		 * List of user groups that are allowed to manage stock items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'type' => [
			/** admin/graphql/resource/stock/type/groups
			 * List of user groups that are allowed to manage stock type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
	'supplier' => [
		/** admin/graphql/resource/supplier/groups
		 * List of user groups that are allowed to manage supplier items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'address' => [
			/** admin/graphql/resource/supplier/address/groups
			 * List of user groups that are allowed to manage supplier address items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
		'lists' => [
			/** admin/graphql/resource/supplier/lists/groups
			 * List of user groups that are allowed to manage supplier lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/supplier/lists/type/groups
				 * List of user groups that are allowed to manage supplier lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/supplier/type/groups
			 * List of user groups that are allowed to manage supplier type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
	'tag' => [
		/** admin/graphql/resource/tag/groups
		 * List of user groups that are allowed to manage tag items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'type' => [
			/** admin/graphql/resource/tag/type/groups
			 * List of user groups that are allowed to manage tag type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
	'text' => [
		/** admin/graphql/resource/text/groups
		 * List of user groups that are allowed to manage text items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'groups' => ['admin', 'editor', 'super'],
		'lists' => [
			/** admin/graphql/resource/text/lists/groups
			 * List of user groups that are allowed to manage text lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
			'type' => [
				/** admin/graphql/resource/text/lists/type/groups
				 * List of user groups that are allowed to manage text lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'groups' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/text/type/groups
			 * List of user groups that are allowed to manage text type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'groups' => ['admin', 'editor', 'super'],
		],
	],
];
