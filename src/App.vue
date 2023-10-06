<template>
	<headerComponent></headerComponent>
	<main>
		<RouterView />
	</main>
</template>

<script setup>
import headerComponent from '@/components/layouts/header.vue';
</script>
