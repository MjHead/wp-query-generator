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
		data: function () {
			return {
				preparedValue: [],
			};
		},
		methods: {
			getDefaultItem: function() {

				var item = {};

				for ( var itemKey in this.field.children ) {
					item[ itemKey ] = this.field.children[ itemKey ].default;
				}

				return item;
			},
			addItem: function() {
				this.preparedValue.push( this.getDefaultItem() );
				this.$emit( 'input', this.preparedValue );
			},
			removeItem: function( index ) {
				this.preparedValue.splice( index, 1 );
				this.$emit( 'input', this.preparedValue );
			},
			setValue: function( val, index, key ) {

				var currentRow = this.preparedValue[ index ];

				Vue.set( currentRow, key, val );
				Vue.set( this.preparedValue, index, currentRow );

				this.$emit( 'input', this.preparedValue );
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
		compareMap: {
			'eq' : '=',
			'neq' : '!=',
			'gth' : '>',
			'geq' : '>=',
			'lth' : '<',
			'leq' : '<=',
			'like' : 'LIKE',
			'not_like' : 'NOT LIKE',
			'in' : 'IN',
			'not_in' : 'NOT IN',
			'between' : 'BETWEEN',
			'not_between' : 'NOT BETWEEN',
			'exists' : 'EXISTS',
			'not_exists' : 'NOT EXISTS',
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
				skip     = [
					'tax_query',
					'tax_query_relation',
					'meta_query',
					'meta_query_relation',
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
			if ( this.result.tax_query && 0 < this.result.tax_query.length ) {
				prepared.tax_query = this.toObject( this.result.tax_query );

				if ( this.result.tax_query_relation ) {
					prepared.tax_query.relation = this.result.tax_query_relation;
				}

			}

			if ( this.result.meta_query && 0 < this.result.meta_query.length ) {
				prepared.meta_query = this.toObject( this.result.meta_query );

				if ( this.result.meta_query_relation ) {
					prepared.tax_query.relation = this.result.meta_query_relation;
				}

			}

			return JSON.stringify( prepared );

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
					rv[ i ] = arr[ i ];

					if ( rv[ i ].compare ) {
						rv[ i ].compare = this.compareMap[ rv[ i ].compare ];
					}

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