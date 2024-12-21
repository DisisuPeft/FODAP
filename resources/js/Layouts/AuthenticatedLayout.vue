<script setup>
import { computed, onBeforeMount, onMounted, ref } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { router, Link, usePage } from "@inertiajs/vue3";
import { FODAPStore } from "@/store/server.js";

const showingNavigationDropdown = ref(false);
const docente = computed(() => usePage().props.info[0]);
const user = computed(() => usePage().props.auth.user);
const admin = computed(() => usePage().props.auth.admin);
const store = FODAPStore();
const notificationObject = ref({});
const menu = ref(false);
const submit_read_notification = () => {
    router.reload();
};

onMounted(() => {
    store.get_is_facilitador(user.value.docente_id);
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav
                class="bg-white dark:bg-gray-900 fixed w-full z-20 top-0 left-0 border-b border-gray-200 dark:border-gray-600"
            >
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-16 w-20 fill-current"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ml-10 lg:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Tablero
                                </NavLink>
                                <template
                                    v-if="
                                        user.role === 1 ||
                                        user.role === 2 ||
                                        admin
                                    "
                                >
                                    <NavLink
                                        :href="route('index.docentes')"
                                        :active="
                                            route().current('index.docentes')
                                        "
                                    >
                                        Docentes
                                    </NavLink>
                                </template>
                                <template v-if="user.role === 3">
                                    <v-tooltip text="Deteccion de Necesidades">
                                        <template v-slot:activator="{ props }">
                                            <!--                                                <v-btn v-bind="props">Tooltip</v-btn>-->
                                            <NavLink
                                                :href="
                                                    route('detecciones.index')
                                                "
                                                :active="
                                                    route().current(
                                                        'detecciones.index'
                                                    )
                                                "
                                                v-bind="props"
                                            >
                                                DN
                                            </NavLink>
                                        </template>
                                    </v-tooltip>
                                </template>
                                <template v-if="user.role === 3">
                                    <NavLink
                                        :href="route('index.cursos.academicos')"
                                        :active="
                                            route().current(
                                                'index.cursos.academicos'
                                            )
                                        "
                                    >
                                        Cursos
                                    </NavLink>
                                </template>
                                <template v-if="user.role === 3">
                                    <NavLink
                                        :href="
                                            route('index.docentes.academicos')
                                        "
                                        :active="
                                            route().current(
                                                'index.docentes.academicos'
                                            )
                                        "
                                    >
                                        Docentes
                                    </NavLink>
                                </template>
                                <template
                                    v-if="user.role === 1 || user.role === 2"
                                >
                                    <v-tooltip text="Deteccion de Necesidades">
                                        <template v-slot:activator="{ props }">
                                            <!--                                                <v-btn v-bind="props">Tooltip</v-btn>-->
                                            <NavLink
                                                :href="
                                                    route('index.detecciones')
                                                "
                                                :active="
                                                    route().current(
                                                        'index.detecciones'
                                                    )
                                                "
                                                v-bind="props"
                                            >
                                                DN
                                            </NavLink>
                                        </template>
                                    </v-tooltip>
                                </template>
                                <template
                                    v-if="user.role === 1 || user.role === 2"
                                >
                                    <NavLink
                                        :href="route('index.desarrollo.cursos')"
                                        :active="
                                            route().current(
                                                'index.desarrollo.cursos'
                                            )
                                        "
                                    >
                                        Cursos
                                    </NavLink>
                                </template>
                                <template v-if="user.role === 4">
                                    <NavLink
                                        :href="route('index.cursos.docentes')"
                                        :active="
                                            route().current(
                                                'index.cursos.docentes'
                                            )
                                        "
                                    >
                                        Cursos
                                    </NavLink>
                                </template>
                                <template v-if="user.role === 4">
                                    <NavLink
                                        :href="route('index.misCursos')"
                                        :active="
                                            route().current('index.misCursos')
                                        "
                                    >
                                        Mis Cursos
                                    </NavLink>
                                </template>
                                <template
                                    v-if="store.this_facilitador === true"
                                >
                                    <NavLink
                                        :href="
                                            route(
                                                'show.facilitadores',
                                                user.docente_id
                                            )
                                        "
                                        :active="
                                            route().current(
                                                'show.facilitadores'
                                            )
                                        "
                                    >
                                        Facilitador
                                    </NavLink>
                                </template>
                                <template
                                    v-if="user.role === 1 || user.role === 2"
                                >
                                    <!--                                    :active="route().current('index.estadisticas')"-->
                                    <NavLink
                                        :href="route('index.estadisticas')"
                                        :active="
                                            route().current(
                                                'index.estadisticas'
                                            )
                                        "
                                    >
                                        Estadisticas
                                    </NavLink>
                                </template>
                                <NavLink
                                    :href="route('profile.edit')"
                                    :active="route().current('profile.edit')"
                                >
                                    Perfil
                                </NavLink>
                                <template
                                    v-if="user.role === 1 || user.role === 2"
                                >
                                    <NavLink
                                        :href="route('parametros.edit')"
                                        :active="
                                            route().current('parametros.edit')
                                        "
                                    >
                                        Configuración
                                    </NavLink>
                                </template>
                                <NavLink
                                    :href="route('logout')"
                                    method="post"
                                    :active="route().current('logout')"
                                    as="button"
                                >
                                    Cerrar Sesión
                                </NavLink>
                                <v-btn class="mt-4" elevation="0">
                                    <template v-if="docente[0] != null">
                                        {{ docente[0].nombre }}
                                    </template>
                                </v-btn>
                            </div>
                        </div>
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="hidden sm:flex sm:items-center sm:ml-6">
                                <div class="ml-3 relative">
                                    <!--                                    <NavLink :href="route('index.notifications')" as="button">-->
                                    <div class="text-center">
                                        <v-menu
                                            v-model="menu"
                                            :close-on-content-click="false"
                                            transition="scale-transition"
                                        >
                                            <template
                                                v-slot:activator="{ props }"
                                            >
                                                <v-btn
                                                    class="text-none"
                                                    v-bind="props"
                                                >
                                                    <v-badge
                                                        color="red"
                                                        :content="
                                                            $page.props.auth
                                                                .usernotifications
                                                        "
                                                    >
                                                        <v-icon
                                                            >mdi-bell</v-icon
                                                        >
                                                    </v-badge>
                                                </v-btn>
                                            </template>

                                            <v-card min-width="300">
                                                <v-card-title
                                                    >Notificaciones</v-card-title
                                                >
                                                <v-list>
                                                    <template
                                                        v-if="
                                                            $page.props.auth
                                                                .usernotify
                                                                .length !== 0
                                                        "
                                                    >
                                                        <v-list-item
                                                            class=""
                                                            v-for="(
                                                                item, i
                                                            ) in $page.props
                                                                .auth
                                                                .usernotify"
                                                            :key="i"
                                                        >
                                                            <v-list-item-title
                                                                >{{
                                                                    item.data
                                                                        .email
                                                                }}
                                                                {{
                                                                    item.data
                                                                        .messegue
                                                                }}</v-list-item-title
                                                            >
                                                            <v-list-item-action>
                                                                <template
                                                                    v-if="
                                                                        $page
                                                                            .props
                                                                            .auth
                                                                            .user
                                                                            .role !==
                                                                        4
                                                                    "
                                                                >
                                                                    <NavLink
                                                                        :href="
                                                                            item
                                                                                .data
                                                                                .route +
                                                                            '/' +
                                                                            item
                                                                                .data
                                                                                .id
                                                                        "
                                                                        type="button"
                                                                        as="button"
                                                                    >
                                                                        <v-chip
                                                                            variant="flat"
                                                                            color="info"
                                                                            prepend-icon="mdi-eye-arrow-right-outline"
                                                                        >
                                                                            Ver
                                                                            notificacion
                                                                        </v-chip>
                                                                    </NavLink>
                                                                </template>
                                                                <template
                                                                    v-if="
                                                                        $page
                                                                            .props
                                                                            .auth
                                                                            .user
                                                                            .role ===
                                                                        4
                                                                    "
                                                                >
                                                                    <NavLink
                                                                        :href="
                                                                            item
                                                                                .data
                                                                                .route
                                                                        "
                                                                        type="button"
                                                                        as="button"
                                                                    >
                                                                        <v-chip
                                                                            variant="flat"
                                                                            color="info"
                                                                            prepend-icon="mdi-eye-arrow-right-outline"
                                                                        >
                                                                            Ver
                                                                            notificacion
                                                                        </v-chip>
                                                                    </NavLink>
                                                                </template>
                                                                <NavLink
                                                                    :href="
                                                                        route(
                                                                            'markNotification'
                                                                        )
                                                                    "
                                                                    type="button"
                                                                    as="button"
                                                                    method="post"
                                                                    :data="{
                                                                        id: item.id,
                                                                    }"
                                                                >
                                                                    <v-chip
                                                                        variant="flat"
                                                                        color="success"
                                                                        prepend-icon="mdi-check-circle"
                                                                    >
                                                                        Marcar
                                                                        como
                                                                        leído
                                                                    </v-chip>
                                                                </NavLink>
                                                            </v-list-item-action>
                                                        </v-list-item>
                                                    </template>
                                                    <template v-else>
                                                        <div
                                                            class="flex justify-center"
                                                        >
                                                            <p class="text-xl">
                                                                Sin
                                                                notificaciones
                                                                recientes.
                                                            </p>
                                                        </div>
                                                    </template>
                                                </v-list>
                                                <v-card-actions>
                                                    <v-spacer></v-spacer>
                                                    <div
                                                        class="flex justify-end mt-12"
                                                    >
                                                        <template
                                                            v-if="
                                                                $page.props.auth
                                                                    .usernotify
                                                                    .length !==
                                                                0
                                                            "
                                                        >
                                                            <NavLink
                                                                :href="
                                                                    route(
                                                                        'markNotification'
                                                                    )
                                                                "
                                                                method="post"
                                                                as="button"
                                                            >
                                                                <v-btn
                                                                    color="success"
                                                                    prepend-icon="mdi-check-circle-outline"
                                                                    width="400"
                                                                    >Marcar como
                                                                    leidas</v-btn
                                                                >
                                                            </NavLink>
                                                        </template>
                                                    </div>
                                                    <!--                                                    <v-btn-->
                                                    <!--                                                        variant="text"-->
                                                    <!--                                                        @click="menu = false"-->
                                                    <!--                                                    >-->
                                                    <!--                                                        Cancel-->
                                                    <!--                                                    </v-btn>-->
                                                    <!--                                                    <v-btn-->
                                                    <!--                                                        color="primary"-->
                                                    <!--                                                        variant="text"-->
                                                    <!--                                                        @click="menu = false"-->
                                                    <!--                                                    >-->
                                                    <!--                                                        Save-->
                                                    <!--                                                    </v-btn>-->
                                                </v-card-actions>
                                            </v-card>
                                        </v-menu>
                                    </div>
                                    <!--                                    </NavLink>-->
                                </div>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center lg:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="lg:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Tablero
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <template v-if="user.role === 1 || user.role === 2">
                                <ResponsiveNavLink
                                    :href="route('parametros.edit')"
                                    :active="route().current('parametros.edit')"
                                >
                                    Configuración
                                </ResponsiveNavLink>
                            </template>
                            <template v-if="user.role === 1 || user.role === 2">
                                <ResponsiveNavLink
                                    :href="route('index.docentes')"
                                    :active="route().current('index.docentes')"
                                >
                                    Docentes
                                </ResponsiveNavLink>
                            </template>
                            <template v-if="user.role === 3">
                                <v-tooltip text="Deteccion de Necesidades">
                                    <template v-slot:activator="{ props }">
                                        <!--                                                <v-btn v-bind="props">Tooltip</v-btn>-->
                                        <ResponsiveNavLink
                                            :href="route('detecciones.index')"
                                            :active="
                                                route().current(
                                                    'detecciones.index'
                                                )
                                            "
                                            v-bind="props"
                                        >
                                            DN
                                        </ResponsiveNavLink>
                                    </template>
                                </v-tooltip>
                            </template>
                            <template v-if="user.role === 3">
                                <ResponsiveNavLink
                                    :href="route('index.cursos.academicos')"
                                    :active="
                                        route().current(
                                            'index.cursos.academicos'
                                        )
                                    "
                                >
                                    Cursos
                                </ResponsiveNavLink>
                            </template>
                            <template v-if="user.role === 3">
                                <ResponsiveNavLink
                                    :href="route('index.docentes.academicos')"
                                    :active="
                                        route().current(
                                            'index.docentes.academicos'
                                        )
                                    "
                                >
                                    Docentes
                                </ResponsiveNavLink>
                            </template>
                            <template v-if="user.role === 1 || user.role === 2">
                                <v-tooltip text="Deteccion de Necesidades">
                                    <template v-slot:activator="{ props }">
                                        <!--                                                <v-btn v-bind="props">Tooltip</v-btn>-->
                                        <ResponsiveNavLink
                                            :href="route('index.detecciones')"
                                            :active="
                                                route().current(
                                                    'index.detecciones'
                                                )
                                            "
                                            v-bind="props"
                                        >
                                            DN
                                        </ResponsiveNavLink>
                                    </template>
                                </v-tooltip>
                            </template>
                            <template v-if="user.role === 1 || user.role === 2">
                                <ResponsiveNavLink
                                    :href="route('index.desarrollo.cursos')"
                                    :active="
                                        route().current(
                                            'index.desarrollo.cursos'
                                        )
                                    "
                                >
                                    Cursos
                                </ResponsiveNavLink>
                            </template>
                            <template v-if="user.role === 4">
                                <ResponsiveNavLink
                                    :href="route('index.cursos.docentes')"
                                    :active="
                                        route().current('index.cursos.docentes')
                                    "
                                >
                                    Cursos
                                </ResponsiveNavLink>
                            </template>
                            <template v-if="user.role === 4">
                                <ResponsiveNavLink
                                    :href="route('index.misCursos')"
                                    :active="route().current('index.misCursos')"
                                >
                                    Mis Cursos
                                </ResponsiveNavLink>
                            </template>
                            <template v-if="store.this_facilitador === true">
                                <ResponsiveNavLink
                                    :href="
                                        route(
                                            'show.facilitadores',
                                            user.docente_id
                                        )
                                    "
                                    :active="
                                        route().current('show.facilitadores')
                                    "
                                >
                                    Facilitador
                                </ResponsiveNavLink>
                            </template>
                            <template v-if="user.role === 1 || user.role === 2">
                                <ResponsiveNavLink
                                    :href="route('index.estadisticas')"
                                    :active="
                                        route().current('index.estadisticas')
                                    "
                                >
                                    Estadisticas
                                </ResponsiveNavLink>
                            </template>
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Cerrar Sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow w-full pt-16" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="">
                <slot />
            </main>
        </div>
    </div>
</template>
