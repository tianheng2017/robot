import { defineStore } from 'pinia'
import { ref } from 'vue'

const useAppStore = defineStore('app', () => {
	const theme = ref('light')
	
	const change = () => {
		if (theme.value == 'light') {
			theme.value = 'dark'
			return
		}
		theme.value = 'light'
	}
	
	return {
		theme,
		change,
	}
}, {
	persist: true
})

export default useAppStore