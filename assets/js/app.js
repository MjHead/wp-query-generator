Vue.component(
	'wp-query-text',
	{
		template: '#wp-query-text',
		props: [ 'field', 'value' ],
		methods: {
			setValue: function( $event ) {

				var returnVal = $event.target.value;

				if ( 'array' === this.field.return && returnVal ) {
					returnVal = returnVal.split( /\,[\s]?/ );
				}

				if ( 'maybearray' === this.field.return && returnVal ) {

					returnVal = returnVal.split( /\,[\s]?/ );

					if ( 1 === returnVal.length ) {
						returnVal = returnVal[0];
					} else if ( 0 === returnVal.length ) {
						returnVal = '';
					}

				}

				this.$emit( 'input', returnVal );
			}
		}
	}
);

Vue.component(
	'wp-query-repeater',
	{
		template: '#wp-query-repeater',
		props: [ 'field', 'value' ],
		methods: {
			getDefaultItem: function() {

				var item = {};

				for ( var itemKey in this.field.children ) {
					item[ itemKey ] = this.field.children[ itemKey ].default;
				}

				return item;
			},
			addItem: function() {
				this.value.push( this.getDefaultItem() );
				this.$emit( 'input', this.value );
			},
			removeItem: function( index ) {
				this.value.splice( index, 1 );
				this.$emit( 'input', this.value );
			},
			setValue: function( val, index, key ) {

				var currentRow = this.value[ index ];

				Vue.set( currentRow, key, val );
				Vue.set( this.value, index, currentRow );

				this.$emit( 'input', this.value );
			},
			currentControl: function( type ) {
				var component = 'wp-query-' + type;
				return component;
			}
		}
	}
);

Vue.component(
	'wp-query-select',
	{
		template: '#wp-query-select',
		props: [ 'field', 'value' ],
		methods: {
			setValue: function( $event ) {
				this.$emit( 'input', $event.target.value );
			}
		}
	}
);

Vue.component(
	'wp-query-checkbox',
	{
		template: '#wp-query-checkbox',
		props: [ 'field', 'value' ],
		data: function () {
			return {
				checked: this.value,
			};
		},
		methods: {
			setValue: function( $event ) {
				this.checked = ! this.checked;
				this.$emit( 'input', this.checked );
			}
		}
	}
);

var WPQG = new Vue({
	el: '#wp_query_generator',
	data: {
		tabs: WPQGTabs,
		fields: WPQGFields,
		activeTab: 'general',
		result: {},
		compareKeys: [
			'exp_eq',
			'exp_neq',
			'exp_gth',
			'exp_geq',
			'exp_lth',
			'exp_leq',
			'exp_like',
			'exp_not_like',
			'exp_in',
			'exp_not_in',
			'exp_between',
			'exp_not_between',
			'exp_exists',
			'exp_not_exists'
		],
		compareMap: {
			'exp_eq' : '=',
			'exp_neq' : '!=',
			'exp_gth' : '>',
			'exp_geq' : '>=',
			'exp_lth' : '<',
			'exp_leq' : '<=',
			'exp_like' : 'LIKE',
			'exp_not_like' : 'NOT LIKE',
			'exp_in' : 'IN',
			'exp_not_in' : 'NOT IN',
			'exp_between' : 'BETWEEN',
			'exp_not_between' : 'NOT BETWEEN',
			'exp_exists' : 'EXISTS',
			'exp_not_exists' : 'NOT EXISTS',
		},
	},
	created: function() {
		var self = this;
		this.fields.map( function ( field ) {
			if ( field.default ) {
				var key = field.id,
				value = field.default;
				Vue.set( self.result, key, value );
			}
		});
	},
	computed: {
		currentTabFields: function() {
			var self = this;
			return this.fields.filter( function ( field ) {
				return field.tab === self.activeTab;
			});
		},
		currentTabTitle: function() {
			for ( var i = 0; i < this.tabs.length; ++i ) {
				if ( this.tabs[ i ].id === this.activeTab ) {
					return this.tabs[ i ].title;
				}
			}
		},
		formatResult: function() {

			var prepared = {},
				result   = '',
				regex    = '',
				self     = this,
				skip     = [
					'tax_query_items',
					'tax_query_relation',
					'meta_query_items',
					'meta_query_relation',
					'date_query_items',
					'date_query_relation',
				];

			for ( var prop in this.result ) {

				if ( 0 <= skip.indexOf( prop ) ) {
					continue;
				}

				if( this.result.hasOwnProperty( prop ) ) {
					prepared[ prop ] = this.prepareField( prop );
				}
			}

			// Add tax, meta and date queries
			if ( this.result.tax_query_items && 0 < this.result.tax_query_items.length ) {
				prepared.tax_query = this.toObject( this.result.tax_query_items );

				if ( this.result.tax_query_relation ) {
					prepared.tax_query.relation = this.result.tax_query_relation;
				}

			}

			if ( this.result.date_query_items && 0 < this.result.date_query_items.length ) {
				prepared.date_query = this.toObject( this.result.date_query_items );

				if ( this.result.date_query_relation ) {
					prepared.date_query.relation = this.result.date_query_relation;
				}

			}

			if ( this.result.meta_query_items && 0 < this.result.meta_query_items.length ) {
				prepared.meta_query = this.toObject( this.result.meta_query_items );

				if ( this.result.meta_query_relation ) {
					prepared.meta_query.relation = this.result.meta_query_relation;
				}

			}

			result = JSON.stringify( prepared );
			regex  = '(' + this.compareKeys.join('|') + ')';
			regex  = new RegExp( regex );

			return result.replace( regex, function( match ) {
				return self.compareMap[ match ];
			} );

		}
	},
	methods: {
		isActiveTab: function( tabID ) {
			return tabID === this.activeTab;
		},
		fieldIsActive: function( field ) {

			if ( ! field.conditions ) {
				return true;
			}

			var isActive = true;

			for ( var condition in field.conditions ) {

				if( ! field.conditions.hasOwnProperty( condition ) ) {
					continue;
				}

				if ( ! this.result[ condition ] ) {
					isActive = false;
				} else {

					if ( 0 > field.conditions[ condition ].indexOf( this.result[ condition ] ) ) {
						isActive = false;
					}

				}
			}

			return isActive;
		},
		setActiveTab: function( tabID ) {
			this.activeTab = tabID;
		},
		currentControl: function( type ) {
			var component = 'wp-query-' + type;
			return component;
		},
		toObject: function( arr ) {

			var rv = {};

			for ( var i = 0; i < arr.length; ++i ) {
				if ( arr[ i ] !== undefined ) {
					Vue.set( rv, i, arr[ i ] );
				}
			}

			return rv;

		},
		copyQuery: function() {

			var codeToCopy = document.querySelector( '#query_to_copy' );

			codeToCopy.setAttribute( 'type', 'text' );
			codeToCopy.select();

			try {
				var successful = document.execCommand( 'copy' );
				if ( successful ) {
					alert( 'Query copied to clipboard' );
				} else {
					alert( 'Oops, unable to copy. Please, select and copy manually.' );
				}
			} catch (err) {
				alert( 'Oops, unable to copy. Please, select and copy manually.' );
			}

			codeToCopy.setAttribute('type', 'hidden');
			window.getSelection().removeAllRanges();
		},
		prepareField: function( prop ) {

			var val = this.result[ prop ];

			switch( prop ) {

				case 'comment_count':

					var matches = val.match( /([<>=!]+)(\d)/ );

					if ( matches ) {
						val = {
							'compare': matches[1],
							'value': matches[2],
						};
					} else {
						val = {
							'compare': '=',
							'value': val,
						};
					}

				break;

			}

			return val;
		},
	},
});