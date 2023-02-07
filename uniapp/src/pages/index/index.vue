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
							<block v-for="(v, i) in appStore.data.list" :key="i">
								<van-cell :border="false" :title-class="i ? '' : 'text-red mt-2.5'" :title="v" />
							</block>
						</van-cell-group>
						<van-empty v-else class="mt-2.5" description="暂无数据" />
					</van-tab>
					<van-tab title="基础配置">
						<van-form @submit="onSubmit">
							<van-cell-group class="pt-2.5">
								<van-field v-model="formData.firstOrder" type="number" name="首单数量" label="首单数量"
									placeholder="首单数量" :rules="[{ required: true, message: '请填写首单数量' }]" />
								<van-field v-model="formData.allAddPositionNum" type="digit" name="最大补仓" label="最大补仓"
									placeholder="最大补仓次数" :rules="[{ required: true, message: '请填写最大补仓次数' }]" />
								<van-field v-model="formData.lever" type="digit" name="杠杆倍数" label="杠杆倍数"
									placeholder="杠杆倍数" :rules="[{ required: true, message: '请填写杠杆倍数' }]" />
								<van-field v-model="formData.profitRatio" type="number" name="止盈率" label="止盈率(%)"
									placeholder="止盈率" :rules="[{ required: true, message: '请填写止盈率' }]" />
								<van-field v-model="formData.stopLossRatio" type="number" name="止损率" label="止损率(%)"
									placeholder="止损率" :rules="[{ required: true, message: '请填写止损率' }]" />
								<van-field v-model="formData.password" type="password" name="操作密码" label="操作密码"
									placeholder="操作密码" :rules="[{ required: true, message: '请填写操作密码' }]" />
							</van-cell-group>
							<div style="margin: 16px;">
								<van-button plain round block type="success" native-type="submit">提交</van-button>
							</div>
						</van-form>
					</van-tab>
				</van-tabs>
				<van-back-top />
				<view class="text-center text-title2 text-sm fixed bottom-2 right-0 left-0">
					在线：{{ appStore.data.other.online || 0 }}</view>
			</view>
		</van-config-provider>
	</view>
</template>
<script setup>
	import {
		onLoad
	} from '@dcloudio/uni-app';
	import {
		computed,
		onBeforeUnmount,
		onMounted,
		reactive,
		ref
	} from 'vue';
	import {
		showLoadingToast,
		showToast
	} from 'vant';
	import useAppStore from '@/store/modules/app';

	// 状态数据
	const appStore = useAppStore();

	// 组件样式定制
	const themeVars = reactive({
		cellLineHeight: '10px'
	});

	// 表单数据
	const formData = reactive({
		firstOrder: appStore.data.other.firstOrder,
		allAddPositionNum: appStore.data.other.allAddPositionNum,
		lever: appStore.data.other.lever,
		profitRatio: appStore.data.other.profitRatio,
		stopLossRatio: appStore.data.other.stopLossRatio,
		password: '123456',
	});
	// 表单提交事件
	const onSubmit = () => {
		showToast('开发中');
	};

	// 字段参数组
	const fieldsMap = [
		[{
				'usdt_init': '初始资金',
				'eq': '账户权益',
                'billingStrategy': '开单策略',
			},
			{
				'firstOrder': '首单数量',
				'profitRatio': '止盈率',
				'stopLossRatio': '止损率',
			},
			{
				'allAddPositionNum': '最大补仓',
				'total_profit': '总盈利',
				'total_profit_ratio': '总盈利率',
			},
			{
				'today_count': '今日做单',
				'today_profit': '今日浮盈',
				'today_profit_ratio': '今日浮盈率',
			},
		],
		[{
                'avgPx': '开仓均价',
				'last': '当前价格',
                'lever': '杠杆倍数',
			},
			{
				'frozenBal': '占用保证金',
				'availEq': '可用保证金',
				'mgnRatio': '保证金率',
			},
			{
				'upl': '本单收益',
				'uplRatio': '本单收益率',
				'addPositionNum': '本单已补仓',
			}
		]
	]

	// 创建webSocket
	uni.connectSocket({
		url: `${import.meta.env.VITE_APP_BASE_URL || ''}`
	});
	// 展示加载层
	const loading = showLoadingToast({
		message: '加载中...',
		forbidClick: true
	});
	// 打开webSocket
	uni.onSocketOpen(function(res) {
		console.log('webSocket连接成功');
		// 发送ping
		uni.sendSocketMessage({
			data: 'ping'
		});
		// 接收数据
		uni.onSocketMessage(function(res) {
			// 格式化数据
			res = JSON.parse(res.data);
			// 获取运行日志
			appStore.data.list = res.data.list;
			// 获取其他数据
			appStore.data.other = res.data.other;
			// 获取到数据，关闭加载层
			loading.close();
		});
	});
	// 打开webSocket失败
	uni.onSocketError(function(res) {
		console.log('webSocket连接失败');
		loading.close();
	});

	// 离开页面关闭websocket
	onBeforeUnmount(() => {
		uni.closeSocket();
	});

	// 图标预加载
	onMounted(() => {
		appStore.preload();
	});
</script>
