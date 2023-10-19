<template>
	<div class="home-page grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
		<div class="counter flex items-center bg-white w-full p-4 rounded-xl shadow-md">
			<div class="icon bg-orange-100 rounded-lg p-3 mr-4 flex items-center justify-center">
				<i class="pi pi-users text-[40px] text-orange-500"></i>
			</div>
			<div>
				<p class="mb-2 text-xl font-medium text-gray-600">Total Users</p>
				<p class="mb-2 text-lg font-semibold text-gray-700">{{ globalStore.home.users || 0 }}</p>
			</div>
		</div>
		<div class="counter flex items-center bg-white w-full p-4 rounded-xl shadow-md">
			<div class="icon bg-blue-100 rounded-lg p-3 mr-4 flex items-center justify-center">
				<i class="pi pi-users text-[40px] text-blue-500"></i>
			</div>
			<div>
				<p class="mb-2 text-xl font-medium text-gray-600">Online Users</p>
				<p class="mb-2 text-lg font-semibold text-gray-700">{{ globalStore.home.online_users || 0 }}</p>
			</div>
		</div>
		<div class="counter flex items-center bg-white w-full p-4 rounded-xl shadow-md">
			<div class="icon bg-indigo-100 rounded-lg p-3 mr-4 flex items-center justify-center">
				<i class="pi pi-database text-[40px] text-indigo-500"></i>
			</div>
			<div>
				<p class="mb-2 text-xl font-medium text-gray-600">Total Programs</p>
				<p class="mb-2 text-lg font-semibold text-gray-700">{{ globalStore.home.programs || 0 }}</p>
			</div>
		</div>
	</div>
</template>

<script setup>
import { onMounted, ref } from 'vue';

import { useGlobalStore } from '@/stores/global';
import globalApi from '@/controllers/global';

const globalStore = useGlobalStore();
const loading = ref(false);

const getHome = () => {
	loading.value = true;
	globalApi
		.getHome()
		.then((response) => {
			globalStore.setHome(response.data);
		})
		.finally(() => {
			loading.value = false;
		});
};

onMounted(() => {
	getHome();
});
</script>
