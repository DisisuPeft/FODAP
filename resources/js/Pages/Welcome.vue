<script setup>
import { Link } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import NavLink from "@/Components/NavLink.vue";
import WelcomeLayout from "@/Layouts/WelcomeLayout.vue";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    can_install: {
        type: Boolean,
    },
});

const nameCards = ref([
    { flex: 4, name: "Departamento de Desarrollo Académico", user_rol: 1 },
    { flex: 4, name: "Jefes Academicos", user_rol: 3 },
    { flex: 4, name: "Docentes", user_rol: 4 },
]);
const selectedCardIndex = ref(null);

const selectCard = (index) => {
    selectedCardIndex.value = index;
};

const isSelected = (index) => {
    return selectedCardIndex.value === index;
};

const mouseLeave = () => {
    selectedCardIndex.value = null;
};

onMounted(() => {});
</script>

<template>
    <WelcomeLayout>
        <div class="flex justify-center">
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6 lg:gap-8 mb-4 mt-16 pt-10"
            >
                <div
                    v-for="(card, index) in nameCards"
                    :key="index"
                    :class="[
                        'p-8',
                        isSelected(index)
                            ? 'bg-blue-900 text-white'
                            : 'bg-white',
                        'rounded-full',
                        'shadow-2xl',
                        'lg:h-64',
                        'flex',
                        'flex-col',
                        'justify-center',
                        'w-64',
                        'h-64',
                        'mt-16',
                    ]"
                    @mouseover="selectCard(index)"
                    @mouseleave="mouseLeave"
                >
                    <Link
                        href="/login"
                        as="button"
                        :data="{ role: card.user_rol }"
                    >
                        <div class="flex items-center justify-center">
                            <h2
                                class="text-xl font-semibold mb-2 text-center lg:text-center"
                            >
                                {{ card.name }}
                            </h2>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </WelcomeLayout>
</template>

<style></style>
