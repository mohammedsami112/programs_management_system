<template>
	<Sidebar v-model:visible="sidebarModal">
		<div class="sidebar-content">
			<div class="top-section">
				<h3 class="mb-3 text-slate-500">Dashboard</h3>
				<ul class="items">
					<li v-for="item in sidebarStore.menu.topMenu.items" :key="item">
						<router-link :to="{ name: item.route }" @click="sidebarModal = false">
							<i :class="item.icon"></i>
							<span>{{ item.title }}</span>
						</router-link>
					</li>
				</ul>
			</div>
			<div class="bottom-section">
				<ul class="items">
					<li>
						<a @click.prevent="logout()" class="logout cursor-pointer">
							<i class="pi pi-sign-out"></i>
							<span>{{ loading ? 'Loading...' : 'Logout' }}</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</Sidebar>
	<button @click="sidebarModal = true"><i class="pi pi-align-justify text-xl"></i></button>
</template>

<script setup>
import { ref } from 'vue';
import Sidebar from 'primevue/sidebar';
import { useSidebarStore } from '@/stores/sidebar';
import { useUserStore } from '@/stores/user';
import { useToast } from 'primevue/usetoast';
import { useRouter } from 'vue-router';
import AuthApi from '@/controllers/auth';

const sidebarStore = useSidebarStore();
const userStore = useUserStore();
const toast = useToast();
const router = useRouter();
const loading = ref(false);
const sidebarModal = ref(false);

const logout = () => {
	loading.value = true;
	AuthApi.logout()
		.then((response) => {
			router.push({ name: 'LoginPage' });
			userStore.logout();
			toast.add({
				severity: 'success',
				detail: response.message,
				life: 3000,
			});
		})
		.catch((error) => {
			toast.add({
				severity: 'error',
				detail: error.response.data.data.error,
				life: 3000,
			});
		})
		.finally(() => {
			loading.value = false;
		});
};
</script>
