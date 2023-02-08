<template>
	<view>
		<van-config-provider :theme="appStore.theme" :theme-vars="themeVars">
			<view class="absolute right-5 top-5" @click="appStore.change()"><van-image :src="appStore.icon" width="35" position="right" /></view>
			<view class="py-5 text-center">
				<van-image width="60" src="/static/logo.png" />
				<view class="light text-4xl mt-2">周易量化</view>
				<view class="mt-4 text-sm text-title2">宇宙万法，大道至简，趋利避害，定转乾坤</view>
			</view>
			<view>
				<van-tabs v-model:active="appStore.tab" :animated="true">
					<block v-for="(item, index) in fieldsMap" :key="index">
						<van-tab :title="!index ? '数据统计' : '当前持仓'">
							<van-grid :column-num="3" v-for="(a, b) in item" :key="b">
								<van-grid-item v-for="(c, d) in a" :key="d">
									<view class="light text-sm font-bold">{{ c }}</view>
									<view class="light text-sm pt-3.5">{{ appStore.data.other[d] }}</view>
								</van-grid-item>
							</van-grid>
						</van-tab>
					</block>
					<van-tab title="运行日志">
						<van-cell-group v-if="appStore.data.list.length">
							<block v-for="(v, i) in appStore.data.list" :key="i"><van-cell :border="false" :title-class="i ? '' : 'text-red mt-2.5'" :title="v" /></block>
						</van-cell-group>
						<van-empty v-else class="mt-2.5" description="暂无数据" />
					</van-tab>
				</van-tabs>
				<view class="text-center text-title2 text-sm fixed bottom-2 right-0 left-0">在线：{{ appStore.data.other.online || 0 }}</view>
			</view>
		</van-config-provider>
	</view>
</template>
<script setup>
import { onLoad } from '@dcloudio/uni-app';
import { computed, onBeforeUnmount, reactive, ref } from 'vue';
import { showLoadingToast, showToast } from 'vant';
import { useAppStore } from '@/store/modules/app';

// store数据
const appStore = useAppStore();

// 定制组件样式
const themeVars = reactive({
	cellLineHeight: '10px'
});

// 数据参数映射
const fieldsMap = [
	[
		{
			usdt_init: '初始资金',
			eq: '账户权益',
			billingStrategy: '开单策略'
		},
		{
			firstOrder: '首单数量',
			profitRatio: '止盈率',
			stopLossRatio: '止损率'
		},
		{
			allAddPositionNum: '最大补仓',
			total_profit: '总盈利',
			total_profit_ratio: '总盈利率'
		},
		{
			today_count: '今日做单',
			today_profit: '今日浮盈',
			today_profit_ratio: '今日浮盈率'
		}
	],
	[
		{
			avgPx: '开仓均价',
			last: '当前价格',
			lever: '杠杆倍数'
		},
		{
			frozenBal: '占用保证金',
			availEq: '可用保证金',
			mgnRatio: '保证金率'
		},
		{
			upl: '本单收益',
			uplRatio: '本单收益率',
			addPositionNum: '本单已补仓'
		}
	]
];

// 监听websocket数据推送
uni.onSocketMessage(function(res) {
	// 格式化数据
	res = JSON.parse(res.data);
	// 获取运行日志
	appStore.data.list = res.data.list;
	// 获取其他数据
	appStore.data.other = res.data.other;
});

// 在onLoad中连接并打开websocket
onLoad(() => {
	// 显示加载层
	showLoadingToast({
		message: '加载中...',
		forbidClick: true
	});
	// 连接websocket
	uni.connectSocket({
		url: `${import.meta.env.VITE_APP_BASE_URL || ''}`
	});
	// 打开webSocket
	uni.onSocketOpen(function(res) {
		// 发送ping请求数据
		uni.sendSocketMessage({
			data: 'ping',
			fail: () => {
				showToast('打开websocket失败，请刷新页面');
			}
		});
	});
	// webSocket已断开
	uni.onSocketClose(function(res) {
		showToast('websocket已断开');
	});
});

// 卸载页面前，关闭websocket、清除定时器
onBeforeUnmount(() => {
	uni.closeSocket();
});
</script>
