<script setup>
import { Button } from "../components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "../components/ui/dropdown-menu";
import { Sheet, SheetContent, SheetTrigger } from "../components/ui/sheet";
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "../components/ui/collapsible";
import {
    Home,
    Package,
    Scroll,
    ShoppingCart,
    HandCoins,
    Tickets,
    Users,
    Package2,
    CircleUser,
    Menu,
    Boxes,
    ChevronDown,
    ChevronRight,
} from "lucide-vue-next";
import { Link, usePage, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const page = usePage();
const user = computed(() => page.props.user);
const permissions = computed(() => page.props.permissions);

// Navigation groups
const navigationGroups = [
    {
        title: "Main",
        items: [
            {
                name: "Dashboard",
                href: "/dashboard",
                icon: Home,
                permission: null,
            },
        ],
    },
    {
        title: "Master Data",
        items: [
            {
                name: "Categories",
                href: "/categories",
                icon: Boxes,
                permission: "categories_view",
            },
            {
                name: "Products",
                href: "/products",
                icon: Package,
                permission: "products_view",
            },
            {
                name: "Vouchers",
                href: "/vouchers",
                icon: Tickets,
                permission: "vouchers_view",
            },
            {
                name: "Members",
                href: "/members",
                icon: Users,
                permission: "members_view",
            },
        ],
    },
    {
        title: "Transactions",
        items: [
            {
                name: "Purchase Order",
                href: "/purchase-order",
                icon: Scroll,
                permission: "po_view",
            },
            {
                name: "Purchasing",
                href: "/purchasing",
                icon: ShoppingCart,
                permission: "purchasing_view",
            },
            {
                name: "POS",
                href: "/pos",
                icon: HandCoins,
                permission: "pos_view",
            },
        ],
    },
    {
        title: "Settings",
        items: [
            {
                name: "Users",
                href: "/users",
                icon: Scroll,
                permissions: null,
            },
        ],
    },
];

// State for collapsible groups
const collapsibleStates = ref(navigationGroups.map(() => true));

const toggleCollapsible = (index) => {
    collapsibleStates.value[index] = !collapsibleStates.value[index];
};

const hasPermission = (permission) => {
    if (permission === null) return true;
    return permissions.value[permission];
};

const hasGroupPermission = (group) => {
    return group.items.some((item) => hasPermission(item.permission));
};

const form = useForm({});

const logout = () => {
    form.post("/logout");
};
</script>

<template>
    <div
        class="grid min-h-screen w-full md:grid-cols-[220px_1fr] lg:grid-cols-[280px_1fr]"
    >
        <div class="hidden border-r bg-muted/40 md:block">
            <div class="flex flex-col h-full max-h-screen gap-2">
                <div
                    class="flex h-14 items-center border-b px-4 lg:h-[60px] lg:px-6"
                >
                    <Link
                        href="/"
                        class="flex items-center gap-2 font-semibold"
                    >
                        <Package2 class="w-6 h-6" />
                        <span class="">Acme Inc</span>
                    </Link>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="secondary"
                                size="icon"
                                class="rounded-full ml-auto"
                            >
                                <CircleUser class="w-5 h-5" />
                                <span class="sr-only">Toggle user menu</span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>{{
                                user.name
                            }}</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem>Settings</DropdownMenuItem>
                            <DropdownMenuItem>Support</DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="logout"
                                >Logout</DropdownMenuItem
                            >
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <div class="flex-1 overflow-auto">
                    <nav class="grid items-start px-2 text-sm font-medium">
                        <div
                            v-for="(group, groupIndex) in navigationGroups"
                            :key="groupIndex"
                        >
                            <Collapsible
                                v-if="hasGroupPermission(group)"
                                :open="collapsibleStates[groupIndex]"
                                @update:open="toggleCollapsible(groupIndex)"
                            >
                                <CollapsibleTrigger
                                    class="flex w-full items-center justify-between py-2 px-4 text-sm font-semibold"
                                >
                                    {{ group.title }}
                                    <ChevronDown
                                        v-if="collapsibleStates[groupIndex]"
                                        class="h-4 w-4"
                                    />
                                    <ChevronRight v-else class="h-4 w-4" />
                                </CollapsibleTrigger>
                                <CollapsibleContent>
                                    <div
                                        v-for="item in group.items"
                                        :key="item.name"
                                    >
                                        <Link
                                            v-if="
                                                hasPermission(item.permission)
                                            "
                                            :href="item.href"
                                            :class="
                                                $page.url === item.href
                                                    ? 'bg-emerald-500 text-gray-100'
                                                    : 'text-muted-foreground'
                                            "
                                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition-all hover:text-primary"
                                        >
                                            <component
                                                :is="item.icon"
                                                class="w-4 h-4"
                                            />
                                            {{ item.name }}
                                        </Link>
                                    </div>
                                </CollapsibleContent>
                            </Collapsible>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <header
                class="flex h-14 lg:h-[60px] items-center gap-4 border-b bg-muted/40 px-6"
            >
                <Sheet>
                    <SheetTrigger asChild>
                        <Button
                            variant="outline"
                            size="icon"
                            class="shrink-0 md:hidden"
                        >
                            <Menu class="w-5 h-5" />
                            <span class="sr-only">Toggle navigation menu</span>
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-[300px] sm:w-[400px]">
                        <nav class="grid gap-2 text-lg font-medium">
                            <div
                                v-for="(group, groupIndex) in navigationGroups"
                                :key="groupIndex"
                                class="py-2"
                            >
                                <Collapsible
                                    v-if="group.items.some((item) => item.show)"
                                    :open="collapsibleStates[groupIndex]"
                                    @update:open="toggleCollapsible(groupIndex)"
                                >
                                    <CollapsibleTrigger
                                        class="flex w-full items-center justify-between py-2 px-4 text-sm font-semibold"
                                    >
                                        {{ group.title }}
                                        <ChevronDown
                                            v-if="collapsibleStates[groupIndex]"
                                            class="h-4 w-4"
                                        />
                                        <ChevronRight v-else class="h-4 w-4" />
                                    </CollapsibleTrigger>
                                    <CollapsibleContent>
                                        <div
                                            v-for="item in group.items"
                                            :key="item.name"
                                        >
                                            <Link
                                                v-if="item.show"
                                                :href="item.href"
                                                :class="
                                                    $page.url === item.href
                                                        ? 'bg-muted text-primary'
                                                        : 'text-muted-foreground'
                                                "
                                                class="flex items-center gap-3 rounded-lg px-4 py-2 transition-all hover:text-primary"
                                            >
                                                <component
                                                    :is="item.icon"
                                                    class="w-5 h-5"
                                                />
                                                {{ item.name }}
                                            </Link>
                                        </div>
                                    </CollapsibleContent>
                                </Collapsible>
                            </div>
                        </nav>
                    </SheetContent>
                </Sheet>
            </header>
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
