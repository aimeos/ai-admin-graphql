<?php

return [
	'attribute' => [
		/** admin/graphql/resource/attribute/delete
		 * List of user groups that are allowed to delete attribute items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/attribute/save
		 * List of user groups that are allowed to create and update attribute items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/attribute/get
		 * List of user groups that are allowed to retrieve attribute items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'lists' => [
			/** admin/graphql/resource/attribute/lists/delete
			 * List of user groups that are allowed to delete attribute lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/attribute/lists/save
			 * List of user groups that are allowed to create and update attribute lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/attribute/lists/get
			 * List of user groups that are allowed to retrieve attribute lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/attribute/lists/type/delete
				 * List of user groups that are allowed to delete attribute lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/attribute/lists/type/save
				 * List of user groups that are allowed to create and update attribute lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/attribute/lists/type/get
				 * List of user groups that are allowed to retrieve attribute lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
		'property' => [
			/** admin/graphql/resource/attribute/property/delete
			 * List of user groups that are allowed to delete attribute property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/attribute/property/save
			 * List of user groups that are allowed to create and update attribute property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/attribute/property/get
			 * List of user groups that are allowed to retrieve attribute property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/attribute/property/type/delete
				 * List of user groups that are allowed to delete attribute property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/attribute/property/type/save
				 * List of user groups that are allowed to create and update attribute property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/attribute/property/type/get
				 * List of user groups that are allowed to retrieve attribute property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/attribute/type/delete
			 * List of user groups that are allowed to delete attribute type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/attribute/type/save
			 * List of user groups that are allowed to create and update attribute type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/attribute/type/get
			 * List of user groups that are allowed to retrieve attribute type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
	'catalog' => [
		/** admin/graphql/resource/catalog/delete
		 * List of user groups that are allowed to delete catalog items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/catalog/save
		 * List of user groups that are allowed to create and update catalog items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/catalog/get
		 * List of user groups that are allowed to retrieve catalog items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'lists' => [
			/** admin/graphql/resource/catalog/lists/delete
			 * List of user groups that are allowed to delete catalog lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/catalog/lists/save
			 * List of user groups that are allowed to create and update catalog lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/catalog/lists/get
			 * List of user groups that are allowed to retrieve catalog lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/catalog/lists/type/delete
				 * List of user groups that are allowed to delete catalog lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/catalog/lists/type/save
				 * List of user groups that are allowed to create and update catalog lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/catalog/lists/type/get
				 * List of user groups that are allowed to retrieve catalog lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
	],
	'coupon' => [
		/** admin/graphql/resource/coupon/delete
		 * List of user groups that are allowed to delete coupon items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/coupon/save
		 * List of user groups that are allowed to create and update coupon items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/coupon/get
		 * List of user groups that are allowed to retrieve coupon items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'code' => [
			/** admin/graphql/resource/coupon/code/delete
			 * List of user groups that are allowed to delete attribute type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/coupon/code/save
			 * List of user groups that are allowed to create and update attribute type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/coupon/code/get
			 * List of user groups that are allowed to retrieve attribute type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
	'customer' => [
		/** admin/graphql/resource/customer/delete
		 * List of user groups that are allowed to delete customer items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'super'],

		/** admin/graphql/resource/customer/save
		 * List of user groups that are allowed to create and update customer items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/customer/get
		 * List of user groups that are allowed to retrieve customer items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'address' => [
			/** admin/graphql/resource/customer/address/delete
			 * List of user groups that are allowed to delete customer address items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/customer/address/save
			 * List of user groups that are allowed to create and update customer address items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/customer/address/get
			 * List of user groups that are allowed to retrieve customer address items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
		'group' => [
			/** admin/graphql/resource/customer/group/delete
			 * List of user groups that are allowed to delete customer group items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/customer/group/save
			 * List of user groups that are allowed to create and update customer group items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/customer/group/get
			 * List of user groups that are allowed to retrieve customer group items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
		'lists' => [
			/** admin/graphql/resource/customer/lists/delete
			 * List of user groups that are allowed to delete customer lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/customer/lists/save
			 * List of user groups that are allowed to create and update customer lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/customer/lists/get
			 * List of user groups that are allowed to retrieve customer lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/customer/lists/type/delete
				 * List of user groups that are allowed to delete customer lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/customer/lists/type/save
				 * List of user groups that are allowed to create and update customer lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/customer/lists/type/get
				 * List of user groups that are allowed to retrieve customer lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
		'property' => [
			/** admin/graphql/resource/customer/property/delete
			 * List of user groups that are allowed to delete customer property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/customer/property/save
			 * List of user groups that are allowed to create and update customer property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/customer/property/get
			 * List of user groups that are allowed to retrieve customer property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/customer/property/type/delete
				 * List of user groups that are allowed to delete customer property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/customer/property/type/save
				 * List of user groups that are allowed to create and update customer property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/customer/property/type/get
				 * List of user groups that are allowed to retrieve customer property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
	],
	'locale' => [
		/** admin/graphql/resource/locale/delete
		 * List of user groups that are allowed to delete locale items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'super'],

		/** admin/graphql/resource/locale/save
		 * List of user groups that are allowed to create and update locale items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'super'],

		/** admin/graphql/resource/locale/get
		 * List of user groups that are allowed to retrieve locale items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'super'],

		'site' => [
			/** admin/graphql/resource/locale/site/delete
			 * List of user groups that are allowed to delete locale site items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['super'],

			/** admin/graphql/resource/locale/site/save
			 * List of user groups that are allowed to create and update locale site items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['super'],

			/** admin/graphql/resource/locale/site/get
			 * List of user groups that are allowed to retrieve locale site items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['super'],
		],
		'language' => [
			/** admin/graphql/resource/locale/language/delete
			 * List of user groups that are allowed to delete locale language items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['super'],

			/** admin/graphql/resource/locale/language/save
			 * List of user groups that are allowed to create and update locale language items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['super'],

			/** admin/graphql/resource/locale/language/get
			 * List of user groups that are allowed to retrieve locale language items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
		'currency' => [
			/** admin/graphql/resource/locale/currency/delete
			 * List of user groups that are allowed to delete locale currency items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['super'],

			/** admin/graphql/resource/locale/currency/save
			 * List of user groups that are allowed to create and update locale currency items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['super'],

			/** admin/graphql/resource/locale/currency/get
			 * List of user groups that are allowed to retrieve locale currency items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
	'media' => [
		/** admin/graphql/resource/media/delete
		 * List of user groups that are allowed to delete media items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/media/save
		 * List of user groups that are allowed to create and update media items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/media/get
		 * List of user groups that are allowed to retrieve media items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'lists' => [
			/** admin/graphql/resource/media/lists/delete
			 * List of user groups that are allowed to delete media lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/media/lists/save
			 * List of user groups that are allowed to create and update media lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/media/lists/get
			 * List of user groups that are allowed to retrieve media lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/media/lists/type/delete
				 * List of user groups that are allowed to delete media lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/media/lists/type/save
				 * List of user groups that are allowed to create and update media lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/media/lists/type/get
				 * List of user groups that are allowed to retrieve media lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
		'property' => [
			/** admin/graphql/resource/media/property/delete
			 * List of user groups that are allowed to delete media property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/media/property/save
			 * List of user groups that are allowed to create and update media property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/media/property/get
			 * List of user groups that are allowed to retrieve media property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/media/property/type/delete
				 * List of user groups that are allowed to delete media property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/media/property/type/save
				 * List of user groups that are allowed to create and update media property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/media/property/type/get
				 * List of user groups that are allowed to retrieve media property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/media/type/delete
			 * List of user groups that are allowed to delete media type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/media/type/save
			 * List of user groups that are allowed to create and update media type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/media/type/get
			 * List of user groups that are allowed to retrieve media type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
	'order' => [
		/** admin/graphql/resource/media/delete
		 * List of user groups that are allowed to delete media items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => [],

		/** admin/graphql/resource/media/save
		 * List of user groups that are allowed to create and update media items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/media/get
		 * List of user groups that are allowed to retrieve media items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],
	],
	'plugin' => [
		/** admin/graphql/resource/plugin/delete
		 * List of user groups that are allowed to delete plugin items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'super'],

		/** admin/graphql/resource/plugin/save
		 * List of user groups that are allowed to create and update plugin items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'super'],

		/** admin/graphql/resource/plugin/get
		 * List of user groups that are allowed to retrieve plugin items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'super'],

		'type' => [
			/** admin/graphql/resource/plugin/type/delete
			 * List of user groups that are allowed to delete plugin type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/plugin/type/save
			 * List of user groups that are allowed to create and update plugin type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/plugin/type/get
			 * List of user groups that are allowed to retrieve plugin type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
	'price' => [
		/** admin/graphql/resource/product/delete
		 * List of user groups that are allowed to delete product items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/product/save
		 * List of user groups that are allowed to create and update product items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/product/get
		 * List of user groups that are allowed to retrieve product items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'lists' => [
			/** admin/graphql/resource/product/lists/delete
			 * List of user groups that are allowed to delete product lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/product/lists/save
			 * List of user groups that are allowed to create and update product lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/product/lists/get
			 * List of user groups that are allowed to retrieve product lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/product/lists/type/delete
				 * List of user groups that are allowed to delete product lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/product/lists/type/save
				 * List of user groups that are allowed to create and update product lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/product/lists/type/get
				 * List of user groups that are allowed to retrieve product lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
		'property' => [
			/** admin/graphql/resource/product/property/delete
			 * List of user groups that are allowed to delete product property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/product/property/save
			 * List of user groups that are allowed to create and update product property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/product/property/get
			 * List of user groups that are allowed to retrieve product property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/product/property/type/delete
				 * List of user groups that are allowed to delete product property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/product/property/type/save
				 * List of user groups that are allowed to create and update product property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/product/property/type/get
				 * List of user groups that are allowed to retrieve product property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/product/type/delete
			 * List of user groups that are allowed to delete product type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/product/type/save
			 * List of user groups that are allowed to create and update product type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/product/type/get
			 * List of user groups that are allowed to retrieve product type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
	'product' => [
		/** admin/graphql/resource/product/delete
		 * List of user groups that are allowed to delete product items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/product/save
		 * List of user groups that are allowed to create and update product items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/product/get
		 * List of user groups that are allowed to retrieve product items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'lists' => [
			/** admin/graphql/resource/product/lists/delete
			 * List of user groups that are allowed to delete product lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/product/lists/save
			 * List of user groups that are allowed to create and update product lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/product/lists/get
			 * List of user groups that are allowed to retrieve product lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/product/lists/type/delete
				 * List of user groups that are allowed to delete product lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/product/lists/type/save
				 * List of user groups that are allowed to create and update product lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/product/lists/type/get
				 * List of user groups that are allowed to retrieve product lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
		'property' => [
			/** admin/graphql/resource/product/property/delete
			 * List of user groups that are allowed to delete product property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/product/property/save
			 * List of user groups that are allowed to create and update product property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/product/property/get
			 * List of user groups that are allowed to retrieve product property items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/product/property/type/delete
				 * List of user groups that are allowed to delete product property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/product/property/type/save
				 * List of user groups that are allowed to create and update product property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/product/property/type/get
				 * List of user groups that are allowed to retrieve product property type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/product/type/delete
			 * List of user groups that are allowed to delete product type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/product/type/save
			 * List of user groups that are allowed to create and update product type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/product/type/get
			 * List of user groups that are allowed to retrieve product type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
	'rule' => [
		/** admin/graphql/resource/rule/delete
		 * List of user groups that are allowed to delete rule items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/rule/save
		 * List of user groups that are allowed to create and update rule items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/rule/get
		 * List of user groups that are allowed to retrieve rule items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'type' => [
			/** admin/graphql/resource/rule/type/delete
			 * List of user groups that are allowed to delete rule type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/rule/type/save
			 * List of user groups that are allowed to create and update rule type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/rule/type/get
			 * List of user groups that are allowed to retrieve rule type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
	'service' => [
		/** admin/graphql/resource/service/delete
		 * List of user groups that are allowed to delete service items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'super'],

		/** admin/graphql/resource/service/save
		 * List of user groups that are allowed to create and update service items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'super'],

		/** admin/graphql/resource/service/get
		 * List of user groups that are allowed to retrieve service items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'super'],

		'lists' => [
			/** admin/graphql/resource/service/lists/delete
			 * List of user groups that are allowed to delete service lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/service/lists/save
			 * List of user groups that are allowed to create and update service lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/service/lists/get
			 * List of user groups that are allowed to retrieve service lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'super'],

			'type' => [
				/** admin/graphql/resource/service/lists/type/delete
				 * List of user groups that are allowed to delete service lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/service/lists/type/save
				 * List of user groups that are allowed to create and update service lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/service/lists/type/get
				 * List of user groups that are allowed to retrieve service lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
	],
	'stock' => [
		/** admin/graphql/resource/stock/delete
		 * List of user groups that are allowed to delete stock items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/stock/save
		 * List of user groups that are allowed to create and update stock items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/stock/get
		 * List of user groups that are allowed to retrieve stock items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'type' => [
			/** admin/graphql/resource/stock/type/delete
			 * List of user groups that are allowed to delete stock type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/stock/type/save
			 * List of user groups that are allowed to create and update stock type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/stock/type/get
			 * List of user groups that are allowed to retrieve stock type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
	'supplier' => [
		/** admin/graphql/resource/supplier/delete
		 * List of user groups that are allowed to delete supplier items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/supplier/save
		 * List of user groups that are allowed to create and update supplier items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/supplier/get
		 * List of user groups that are allowed to retrieve supplier items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'address' => [
			/** admin/graphql/resource/supplier/address/delete
			 * List of user groups that are allowed to delete supplier address items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/supplier/address/save
			 * List of user groups that are allowed to create and update supplier address items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/supplier/address/get
			 * List of user groups that are allowed to retrieve supplier address items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
		'lists' => [
			/** admin/graphql/resource/supplier/lists/delete
			 * List of user groups that are allowed to delete supplier lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/supplier/lists/save
			 * List of user groups that are allowed to create and update supplier lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/supplier/lists/get
			 * List of user groups that are allowed to retrieve supplier lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/supplier/lists/type/delete
				 * List of user groups that are allowed to delete supplier lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/supplier/lists/type/save
				 * List of user groups that are allowed to create and update supplier lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/supplier/lists/type/get
				 * List of user groups that are allowed to retrieve supplier lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
	],
	'tag' => [
		/** admin/graphql/resource/tag/delete
		 * List of user groups that are allowed to delete tag items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/tag/save
		 * List of user groups that are allowed to create and update tag items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/tag/get
		 * List of user groups that are allowed to retrieve tag items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'type' => [
			/** admin/graphql/resource/tag/type/delete
			 * List of user groups that are allowed to delete tag type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/tag/type/save
			 * List of user groups that are allowed to create and update tag type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/tag/type/get
			 * List of user groups that are allowed to retrieve tag type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
	'text' => [
		/** admin/graphql/resource/text/delete
		 * List of user groups that are allowed to delete text items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'delete' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/text/save
		 * List of user groups that are allowed to create and update text items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'save' => ['admin', 'editor', 'super'],

		/** admin/graphql/resource/text/get
		 * List of user groups that are allowed to retrieve text items
		 *
		 * @param array List of user group names
		 * @since 2022.10
		 */
		'get' => ['admin', 'editor', 'super'],

		'lists' => [
			/** admin/graphql/resource/text/lists/delete
			 * List of user groups that are allowed to delete text lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/text/lists/save
			 * List of user groups that are allowed to create and update text lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'editor', 'super'],

			/** admin/graphql/resource/text/lists/get
			 * List of user groups that are allowed to retrieve text lists items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],

			'type' => [
				/** admin/graphql/resource/text/lists/type/delete
				 * List of user groups that are allowed to delete text lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'delete' => ['admin', 'super'],

				/** admin/graphql/resource/text/lists/type/save
				 * List of user groups that are allowed to create and update text lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'save' => ['admin', 'super'],

				/** admin/graphql/resource/text/lists/type/get
				 * List of user groups that are allowed to retrieve text lists type items
				 *
				 * @param array List of user group names
				 * @since 2022.10
				 */
				'get' => ['admin', 'editor', 'super'],
			],
		],
		'type' => [
			/** admin/graphql/resource/text/type/delete
			 * List of user groups that are allowed to delete text type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'delete' => ['admin', 'super'],

			/** admin/graphql/resource/text/type/save
			 * List of user groups that are allowed to create and update text type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'save' => ['admin', 'super'],

			/** admin/graphql/resource/text/type/get
			 * List of user groups that are allowed to retrieve text type items
			 *
			 * @param array List of user group names
			 * @since 2022.10
			 */
			'get' => ['admin', 'editor', 'super'],
		],
	],
];
