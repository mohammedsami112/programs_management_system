<template>
	<header class="navbar">
		<div class="navbar-content">
			<div class="user">
				<div class="avatar"><img src="#" alt="" v-if="userStore.user.avatar" /></div>
				<div class="username">{{ userStore.user.name }}</div>
			</div>
			<sidebar></sidebar>
		</div>
	</header>
</template>

<script setup>
import sidebar from '@/components/layouts/sidebar.vue';
import { useUserStore } from '@/stores/user';

const userStore = useUserStore();
</script>
