<div class="wp-query-tabs">
	<div class="wp-query-tabs__nav">
		<div v-for="item in tabs" @click="setActiveTab( item.id )" :class="['wp-query-tabs__nav-item', { 'wp-query-tabs__nav-item--active': isActiveTab( item.id ) }]">
			{{ item.label }}
		</div>
	</div>
	<div class="wp-query-tabs__content">
		<div class="wp-query-tabs__content-row" v-for="field in currentTabFields">
			<component v-bind:is="currentControl( field.type )" :field="field" v-model="result[ field.id ]"></component>
		</div>
	</div>
</div>
<div class="wp-query-result">
	{{ formatResult }}
</div>