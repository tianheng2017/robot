<script>
export default {
  onLaunch: function () {
    console.log('App Launch')
  },
  onShow: function () {
    console.log('App Show')
  },
  onHide: function () {
    console.log('App Hide')
  },
}
</script>

<style>
/*每个页面公共css */
.van-theme-dark body {
	background-color: black;
}

.van-theme-dark body .light {
	color: #fff;
}
</style>
