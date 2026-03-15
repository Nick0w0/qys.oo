<template>
	<view class="waterfall-flow-component">
		<view class="waterfall-flow-list" id="rw2">
			<view class="waterfall-flow" :style="{'width': `${100/column}%`}" v-for="(columnItem,columnIndex) of column" :id="('waterfall-flow-'+(columnIndex+1))">
				<view class="waterfall-flow-item" v-for="(item,index) of all_list[`column_list_${(columnIndex+1)}`]" :index="index">
					<slot :data="item" :index="index"></slot>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default{
		props:{
			list:{
				type:Array,
				default:[]
			},
			column:{
				type:[Number],
				default:2,
			}
		},
		data(){
			return{
				all_list:{},
				list_length:0,
				flag:false
			}
		},
		watch:{
			list:{
				handler(list){
					this.readyList(list)
				},
				deep:true,
				immediate:true
			}
		},
		methods:{
			/**
			 * Ready the list
			 */
			async readyList(list){
				if(this.flag){
					// Due to device performance issues, this can cause data clutter and requires throttling
					return
				}
				this.flag = true
				if( list && list.length > 0 ){
					let newList = list.slice(this.list_length)
					for(let i in newList){
						// One by one
						// await this.awaitFun(1000)
						await this.setList(newList[i])
					}
					this.flag = false
					this.list_length = list.length
					this.$emit('success',{current_list:newList})
				}
			},
			/**
			 * await 
			 */
			awaitFun(timeout){
				return new Promise((resolve,reject)=>{
					setTimeout(()=>{
						resolve()
					},timeout || 0)
				})
			},
			/**
			 * Reload the list
			 */
			reLoadList(){
				this.resetList()
				this.$nextTick(()=>{
					this.readyList(this.list)
				})
			},
			/**
			 * Empty the list
			 */
			resetList(){
				for(let i in this.all_list){
					this.all_list[i] = []
				}
				this.list_length = 0
				this.$forceUpdate()
			},
			/**
			 * Set the list
			 * @param {Object} list
			 */
			setList(list){
				return new Promise((resolve,reject)=>{
					this.$nextTick(async()=>{
						let all_list = {}
						
						let min_ele = {
							name:'',
							height:0
						}
						for(let i =0;i<this.column;i++){
							let name = `column_list_${i+1}`
							all_list[name] = await this.getHeight(`#waterfall-flow-${i+1}`)
							
							if(i == 0){
								min_ele.name = name
								min_ele.height = all_list[name]
							}
						}
						
						for(let i in all_list){
							if(all_list[i] < min_ele.height){
								min_ele.height = all_list[i]
								min_ele.name = i
							}
						}
						if(this.all_list[min_ele.name]){
							this.all_list[min_ele.name].push(list)
						}else{
							this.all_list[min_ele.name] = [list]
						}
						
						this.$forceUpdate()
						resolve()
					})
				})
			},
			/**
			 * Get element height
			 * @param {Object} type
			 */
			getHeight(type){
				return new Promise((resolve,reject)=>{
					uni.createSelectorQuery().in(this).select(type).boundingClientRect((data)=>{
						if(data){
							resolve(data.height)
						}else{
							resolve(0)
						}
			　　    }).exec()
				})
			}
		}
	}
</script>

<style>
.waterfall-flow{
	overflow: hidden;
}
.waterfall-flow-item{
	display: inline-block;
	overflow: hidden;
	vertical-align: top;
}
.waterfall-flow-list{
	align-items:flex-start;
	display: flex;
	justify-content: space-between;
}
</style>
