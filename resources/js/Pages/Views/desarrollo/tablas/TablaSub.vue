<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import NavLink from "@/Components/NavLink.vue";
const props = defineProps({
    sub: {
        type: Array,
    },
    modelValue: {
        type: Boolean,
    },
});
const open = ref(false);
const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <!-- <div class="grid grid-rows-2">
        <div class="flex justify-center h-[80px]"> -->
    <v-table fixed-header height="120px" hover>
        <thead>
            <tr>
                <th class="text-center">Nombre del(a) subdirector(a)</th>
                <th class="text-center">Editar</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="s in props.sub" :key="s.id">
                <td class="text-center">{{ s.name }}</td>
                <td class="text-center">
                    <primary-button @click="emit('update:modelValue', true)">
                        <v-icon>mdi-pencil</v-icon>
                    </primary-button>
                </td>
            </tr>
        </tbody>
    </v-table>
    <!-- </div>
        <div class="flex justify-end h-[40px]" v-if="props.sub.length === 0">
            <button
                class="rounded-lg p-2 bg-sky-500 hover:bg-sky-700"
                @click="open = true"
            >
                Crear
            </button>
            <DialogSubdireccion :></DialogSubdireccion>
        </div> -->
    <!-- </div> -->
</template>

<style scoped></style>
