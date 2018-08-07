<div class="wp-query-tabs">
	<div class="wp-query-tabs__nav">
		<div v-for="item in tabs" @click="setActiveTab( item.id )" :class="['wp-query-tabs__nav-item', { 'wp-query-tabs__nav-item--active': isActiveTab( item.id ) }]">
			{{ item.label }}
		</div>
	</div>
	<div class="wp-query-tabs__content">
		<h4>{{ currentTabTitle }}</h4>
		<div class="wp-query-tabs__content-row" v-for="field in currentTabFields">
			<component
				:is="currentControl( field.type )"
				:field="field"
				v-model="result[ field.id ]"
				v-if="fieldIsActive( field )"
			>
			</component>
		</div>
	</div>
</div>
<div class="wp-query-result">
	<div class="wp-query-result__header">
		<div class="wp-query-result__title">
			Generated Query
		</div>
		<div class="wp-query-switcher">
			<div
				v-for="( formatLabel, format ) in resultFormats"
				:class="[ 'wp-query-switcher__item', { 'wp-query-switcher-active': resultFormat === format } ]"
				@click="resultFormat = format"
			>{{ formatLabel }}</div>
		</div>
	</div>
	<div class="wp-query-result__content">{{ formatResult }}</div>
	<div class="wp-query-result__actions">
		<button
			v-clipboard:copy="formatResult"
			v-clipboard:success="copySuccess"
			v-clipboard:error="copyError"
		>Copy to Clipboard</button>
	</div>
</div>