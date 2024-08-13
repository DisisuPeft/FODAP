<script setup>
import {computed, onMounted, onUnmounted, ref, watch} from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    color: {
        type: String,
        default: "bg-white",
    },
    persistent: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['close']);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = null;
        }
        // console.log(modal.value)
        saveModalState()
    }
);

const modal = ref(false)
const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show && props.persistent) {
        close();
    }
};
const loadModalState = () => {
    const modalState = localStorage.getItem('modalState');
    props.show = modalState === 'true';
};
const saveModalState = () => {
    // modal.value = props.show
    if (props.persistent){

    }else{
        localStorage.setItem('modalState', props.show);
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape)
    saveModalState()
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
    // saveModalState()
});

// watch(props.show, () => {
//     console.log(modal.value)
//     saveModalState()
// })

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <teleport to="body">
        <transition leave-active-class="duration-200">
            <div v-show="show" class="fixed inset-0 overflow-y-auto px-4 py-16 sm:px-0 z-50" scroll-region>
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="show" class="fixed inset-0 transform transition-all" @click="close">
                        <div class="absolute inset-0 bg-gray-200 blur-2xl opacity-75" />
                    </div>
                </transition>

                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-show="show"
                        :class="['mb-6', color, 'rounded-lg', 'overflow-hidden', 'shadow-xl', 'transform', 'transition-all', 'sm:w-full', 'sm:mx-auto', maxWidthClass]"
                    >
                        <slot v-if="show" />
                    </div>
                </transition>
            </div>
        </transition>
    </teleport>
</template>
