<template>
	<headerComponent></headerComponent>
	<main class="mt-10 px-8">
		<h1 class="text-4xl font-bold text-slate-600 mb-8">{{ router.currentRoute.value.meta.pageTitle }}</h1>
		<RouterView />
	</main>
</template>

<script setup>
import '@/assets/pages.scss';
import headerComponent from '@/components/layouts/header.vue';
import { useRouter } from 'vue-router';

const router = useRouter();
</script>
