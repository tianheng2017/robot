import {
	defineStore
} from 'pinia'
import {
	computed,
	reactive,
	ref
} from 'vue'

export const useAppStore = defineStore('app', () => {
	// 当前主题
	const theme = ref('light')
	// 主题切换
	const change = () => {
		if (theme.value == 'light') {
			theme.value = 'dark'
			return
		}
		theme.value = 'light'
	}
	// 当前图标
	const icon = computed(() => {
		if (theme.value == 'light') {
			return '/static/dark-theme.svg'
		}
		return '/static/light-theme.svg'
	})
	// 页面数据
	const data = reactive({
		list: {},
		other: {},
	})
	// 当前标签栏
	const tab = ref(0)

	return {
		theme,
		change,
		data,
		tab,
		icon,
	}
}, {
	// store持久化
	persist: {
		key: "store",
		// 只持久化必要数据，免得刷新页面后读到了缓存数据，还以为没亏钱~^_^~
		paths: ['theme', 'tab', 'icon'],
	}
})