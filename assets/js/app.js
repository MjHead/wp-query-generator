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

				this.$emit( 'input', returnVal );
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
				this.$emit( 'input', $event.target.value )
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
			}
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
  },
  mounted: function() {
  	var self = this;
  	this.fields.map( function ( field ) {
		if ( field.default ) {
			var key = field.id,
				value = field.default;

			Vue.set( self.result, key, value );
		}
	})
  },
  computed: {
  	currentTabFields: function() {
  		var self = this;
  		return this.fields.filter( function ( field ) {
			return field.tab === self.activeTab;
		});
  	},
  	formatResult: function() {
  		return JSON.stringify( this.result );
  	}
  },
  methods: {
  	isActiveTab: function( tabID ) {
  		return tabID === this.activeTab;
  	},
  	setActiveTab: function( tabID ) {
  		this.activeTab = tabID;
  	},
  	currentControl: function( type ) {  		
  		var component = 'wp-query-' + type;
  		return component;
  	}
  },
});