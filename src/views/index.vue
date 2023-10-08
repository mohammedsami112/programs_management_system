<template>
	<headerComponent></headerComponent>
	<main>
		<RouterView />
	</main>
</template>

<script setup>
import '@/assets/pages.scss';
import headerComponent from '@/components/layouts/header.vue';
</script>
