<template>
	<view>
		<van-config-provider :theme="appStore.theme" :theme-vars="themeVars">
			<view style="position: absolute;right: 20px;top: 20px;" @click="appStore.change()">
				<van-image
				  :src="icon"
				  width="35"
				  position="right"
				/>
			</view>
			<view class="py-5 text-center">
				<van-image width="60" src="/static/logo.png" />
				<view class="light text-4xl mt-2">周易量化</view>
				<view class="mt-4 text-sm text-title2">宇宙万法，大道至简，趋利避害，定转乾坤</view>
			</view>
			<view>
				<van-tabs v-model:active="tab" :animated="true">
					<van-tab title="数据统计">
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-sm font-bold">初始资金</view>
								<view class="light text-sm pt-3">{{ data.other.usdt_init }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">账户权益</view>
								<view class="light text-sm pt-3">{{ data.other.eq }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">开单策略</view>
								<view class="light text-sm pt-3">随机多空</view>
							</van-grid-item>
						</van-grid>
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-sm font-bold">首单数量</view>
								<view class="light text-sm pt-3">{{ data.other.firstOrder }} ETH</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">止盈率</view>
								<view class="light text-sm pt-3">{{ data.other.profitRatio }} %</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">止损率</view>
								<view class="light text-sm pt-3">{{ data.other.stopLossRatio }} %</view>
							</van-grid-item>
						</van-grid>
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-xs font-bold">止损补仓</view>
								<view class="light text-xs pt-3">{{ data.other.allAddPositionNum }} 次</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-xs font-bold">总盈利</view>
								<view class="light text-xs pt-3">{{ data.other.total_profit }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-xs font-bold">总盈利率</view>
								<view class="light text-xs pt-3">{{ data.other.total_profit_ratio }} %</view>
							</van-grid-item>
						</van-grid>
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-xs font-bold">今日盈利</view>
								<view class="light text-xs pt-3">{{ data.other.today_profit }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-xs font-bold">今日盈率</view>
								<view class="light text-xs pt-3">{{ data.other.today_profit_ratio }} %</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-xs font-bold">今日做单</view>
								<view class="light text-xs pt-3">{{ data.other.today_count }} 笔</view>
							</van-grid-item>
						</van-grid>
					</van-tab>
					<van-tab title="当前持仓">
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-sm font-bold">开仓均价</view>
								<view class="light text-sm pt-3">{{ data.other.avgPx }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">当前价格</view>
								<view class="light text-sm pt-3">{{ data.other.last }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">杠杆倍数</view>
								<view class="light text-sm pt-3">{{ data.other.lever }} x</view>
							</van-grid-item>
						</van-grid>
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-sm font-bold">初始保证金</view>
								<view class="light text-sm pt-3">{{ data.other.frozenBal }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">可用保证金</view>
								<view class="light text-sm pt-3">{{ data.other.availEq }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">保证金率</view>
								<view class="light text-sm pt-3">{{ data.other.mgnRatio }} %</view>
							</van-grid-item>
						</van-grid>
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-sm font-bold">本单收益</view>
								<view class="light text-sm pt-3">{{ data.other.upl }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">本单收益率</view>
								<view class="light text-sm pt-3">{{ data.other.uplRatio }} %</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">已补仓</view>
								<view class="light text-sm pt-3">{{ data.other.addPositionNum }} 次</view>
							</van-grid-item>
						</van-grid>
					</van-tab>
					<van-tab title="执行日志">
						<van-cell-group>
							<block v-for="(v, i) in execLog" :key="i">
								<van-cell :border="false" :title-class="i ? '' : 'text-red'" :title="v" />
							</block>
						</van-cell-group>
					</van-tab>
				</van-tabs>
				<van-back-top />
				<view class="text-center text-title2 text-sm fixed bottom-2 right-0 left-0">在线：1人</view>
			</view>
		</van-config-provider>
	</view>
</template>
<script setup>
	import {
		onUnload
	} from '@dcloudio/uni-app';
	import {
		computed,
		reactive,
		ref
	} from 'vue'
	import useAppStore from '@/store/modules/app'
	
	const appStore = useAppStore()

	// 主题和组件样式
	const themeVars = reactive({
		cellLineHeight: '10px'
	})
	const icon = computed(() => {
		if (appStore.theme == 'light') {
			return '/static/dark-theme.svg'
		}
		return '/static/light-theme.svg'
	})

	// 标签栏
	const tab = ref(0)

	// 页面数据
	const data = reactive({
		// 执行日志
		list: {},
		// 其他数据
		other: {},
	})

	// 执行日志-计算属性（缓存）
	const execLog = computed(() => {
		return data.list
	})

	// 创建webSocket
	uni.connectSocket({
		url: `${import.meta.env.VITE_APP_BASE_URL || ''}`
	})
	// 打开webSocket
	uni.onSocketOpen(function(res) {
		console.log('webSocket连接成功')
		// 发送ping
		uni.sendSocketMessage({
			data: 'ping'
		})
		// 接收数据
		uni.onSocketMessage(function(res) {
			// 格式化数据
			res = JSON.parse(res.data)
			// 获取执行日志
			data.list = res.data.list
			// 获取其他数据
			data.other = res.data.other
		})
	})
	// 打开webSocket失败
	uni.onSocketError(function(res) {
		console.log('webSocket连接失败')
	})

	// 离开页面关闭websocket
	onUnload(() => {
		uni.closeSocket()
	})
</script>
<style scoped>
	.van-theme-dark body {
		background-color: black;
	}
	.van-theme-dark body .light{
		color: #fff;
	}
</style>