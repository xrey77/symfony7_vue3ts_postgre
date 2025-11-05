<template>
    <h1>Not Found</h1>
</template>

<script lang="ts">
import { definecomponent, ref } from 'vue';

export default definecomponent({
    name: 'NotFound-Page',
    mounted() {
        const currentPath = ref(window.location.hash)
        alert(currentPath);
        window.addEventListener('hashchange', () => {
        currentPath.value = window.location.hash
        })        
    },
});

</script>