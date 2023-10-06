<template>
	<Sidebar v-model:visible="sidebarModal">
		<div class="sidebar-content">
			<div class="top-section">
				<h3 class="mb-3 text-slate-500">Dashboard</h3>
				<ul class="items">
					<li v-for="item in sidebarStore.menu.topMenu.items" :key="item">
						<router-link :to="{ name: item.route }">
							<i :class="item.icon"></i>
							<span>{{ item.title }}</span>
						</router-link>
					</li>
				</ul>
			</div>
			<div class="bottom-section">
				<ul class="items">
					<li>
						<a href="#" class="logout">
							<i class="pi pi-sign-out"></i>
							<span>Logout</span>
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

const sidebarStore = useSidebarStore();
const sidebarModal = ref(false);
</script>
