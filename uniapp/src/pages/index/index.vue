<template>
	<view>
		<van-config-provider :theme="appStore.theme" :theme-vars="themeVars">
			<view class="absolute right-5 top-5" @click="appStore.change()">
				<van-image :src="appStore.icon" width="35" position="right" />
			</view>
			<view class="py-5 text-center">
				<van-image width="60" src="/static/logo.png" />
				<view class="light text-4xl mt-2">周易量化</view>
				<view class="mt-4 text-sm text-title2">宇宙万法，大道至简，趋利避害，定转乾坤</view>
			</view>
			<view>
				<van-tabs v-model:active="appStore.tab" :animated="true">
					<van-tab title="数据统计">
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-base font-bold">初始资金</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.usdt_init }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">账户权益</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.eq }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">开单策略</view>
								<view class="light text-sm pt-3.5">随机多空</view>
							</van-grid-item>
						</van-grid>
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-sm font-bold">首单数量</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.firstOrder }} ETH</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">止盈率</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.profitRatio }} %</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">止损率</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.stopLossRatio }} %</view>
							</van-grid-item>
						</van-grid>
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-xs font-bold">止损补仓</view>
								<view class="light text-xs pt-3.5">{{ appStore.data.other.allAddPositionNum }} 次</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-xs font-bold">总盈利</view>
								<view class="light text-xs pt-3.5">{{ appStore.data.other.total_profit }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-xs font-bold">总盈利率</view>
								<view class="light text-xs pt-3.5">{{ appStore.data.other.total_profit_ratio }} %</view>
							</van-grid-item>
						</van-grid>
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-xs font-bold">今日盈利</view>
								<view class="light text-xs pt-3.5">{{ appStore.data.other.today_profit }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-xs font-bold">今日盈率</view>
								<view class="light text-xs pt-3.5">{{ appStore.data.other.today_profit_ratio }} %</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-xs font-bold">今日做单</view>
								<view class="light text-xs pt-3.5">{{ appStore.data.other.today_count }} 笔</view>
							</van-grid-item>
						</van-grid>
					</van-tab>
					<van-tab title="当前持仓">
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-sm font-bold">开仓均价</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.avgPx }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">当前价格</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.last }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">杠杆倍数</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.lever }} x</view>
							</van-grid-item>
						</van-grid>
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-sm font-bold">初始保证金</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.frozenBal }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">可用保证金</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.availEq }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">保证金率</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.mgnRatio }} %</view>
							</van-grid-item>
						</van-grid>
						<van-grid :column-num="3">
							<van-grid-item>
								<view class="light text-sm font-bold">本单收益</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.upl }}</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">本单收益率</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.uplRatio }} %</view>
							</van-grid-item>
							<van-grid-item>
								<view class="light text-sm font-bold">已补仓</view>
								<view class="light text-sm pt-3.5">{{ appStore.data.other.addPositionNum }} 次</view>
							</van-grid-item>
						</van-grid>
					</van-tab>
					<van-tab title="运行日志">
						<van-cell-group>
							<block v-for="(v, i) in appStore.data.list" :key="i">
								<van-cell :border="false" :title-class="i ? '' : 'text-red mt-2.5'" :title="v" />
							</block>
						</van-cell-group>
					</van-tab>
				</van-tabs>
				<van-back-top />
				<view class="text-center text-title2 text-sm fixed bottom-2 right-0 left-0">
					在线：{{ appStore.data.other.online || 0}} 人</view>
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
		onMounted,
		reactive,
		ref
	} from 'vue'
	import useAppStore from '@/store/modules/app'

	// 状态数据
	const appStore = useAppStore()

	// 组件样式定制
	const themeVars = reactive({
		cellLineHeight: '10px'
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
			// 获取运行日志
			appStore.data.list = res.data.list
			// 获取其他数据
			appStore.data.other = res.data.other
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
	
	// 图标预加载
	onMounted(() => {
		appStore.preload()
	})
</script>
<style scoped>
	.van-theme-dark body {
		background-color: black;
	}

	.van-theme-dark body .light {
		color: #fff;
	}
</style>
