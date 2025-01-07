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
    CircleUser,
    Home,
    Menu,
    Package,
    Package2,
    Scroll,
    ShoppingCart,
    HandCoins,
    Tickets,
    Users,
} from "lucide-vue-next";
import { Link, usePage, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const user = computed(() => page.props.user);
const permissions = computed(() => page.props.permissions);

// Computed properties based on permissions
const canViewProducts = computed(() => permissions.value.products_view);
const canViewPO = computed(() => permissions.value.po_view);
const canViewPurchasing = computed(() => permissions.value.purchasing_view);
const canViewVouchers = computed(() => permissions.value.vouchers_view);
const canViewMembers = computed(() => permissions.value.members_view);
const canViewPOS = computed(() => permissions.value.pos_view);

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
                <div class="flex-1">
                    <nav
                        class="grid items-start px-2 text-sm font-medium lg:px-4"
                    >
                        <Link
                            href="/dashboard"
                            :class="
                                $page.url === '/dashboard'
                                    ? 'bg-muted text-primary'
                                    : 'text-muted-foreground'
                            "
                            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
                        >
                            <Home class="w-4 h-4" />
                            Dashboard
                        </Link>
                        <Link
                            v-if="canViewProducts"
                            href="/products"
                            :class="
                                $page.url === '/'
                                    ? 'bg-muted text-primary'
                                    : 'text-muted-foreground'
                            "
                            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
                        >
                            <Package class="w-4 h-4" />
                            Products
                        </Link>
                        <Link
                            v-if="canViewPO"
                            href="/purchase-order"
                            :class="
                                $page.url === '/'
                                    ? 'bg-muted text-primary'
                                    : 'text-muted-foreground'
                            "
                            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
                        >
                            <Scroll class="w-4 h-4" />
                            Purchase Order
                        </Link>
                        <Link
                            v-if="canViewPurchasing"
                            href="/purchasing"
                            :class="
                                $page.url === '/'
                                    ? 'bg-muted text-primary'
                                    : 'text-muted-foreground'
                            "
                            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
                        >
                            <ShoppingCart class="w-4 h-4" />
                            Purchasing
                        </Link>
                        <Link
                            v-if="canViewPOS"
                            href="/pos"
                            :class="
                                $page.url === '/'
                                    ? 'bg-muted text-primary'
                                    : 'text-muted-foreground'
                            "
                            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
                        >
                            <HandCoins class="w-4 h-4" />
                            POS
                        </Link>
                        <Link
                            v-if="canViewVouchers"
                            href="/vouchers"
                            :class="
                                $page.url === '/'
                                    ? 'bg-muted text-primary'
                                    : 'text-muted-foreground'
                            "
                            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
                        >
                            <Tickets class="w-4 h-4" />
                            Vouchers
                        </Link>
                        <Link
                            v-if="canViewMembers"
                            href="/members"
                            :class="
                                $page.url === '/'
                                    ? 'bg-muted text-primary'
                                    : 'text-muted-foreground'
                            "
                            class="flex items-center gap-3 px-3 py-2 transition-all rounded-lg hover:text-primary"
                        >
                            <Users class="w-4 h-4" />
                            Members
                        </Link>
                    </nav>
                </div>
            </div>
        </div>
        <div class="flex flex-col">
            <div class="flex flex-col justify-between">
                <header
                    class="flex h-14 items-center gap-4 border-b bg-muted/40 px-4 lg:h-[60px] lg:px-6"
                >
                    <Sheet>
                        <SheetTrigger as-child>
                            <Button
                                variant="outline"
                                size="icon"
                                class="shrink-0 md:hidden"
                            >
                                <Menu class="w-5 h-5" />
                                <span class="sr-only"
                                    >Toggle navigation menu</span
                                >
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="flex flex-col">
                            <nav class="grid gap-2 text-lg font-medium">
                                <Link
                                    href="#"
                                    class="flex items-center gap-2 text-lg font-semibold"
                                >
                                    <Package2 class="w-6 h-6" />
                                    <span class="sr-only">Acme Inc</span>
                                </Link>
                                <Link
                                    href="/dashboard"
                                    class="mx-[-0.65rem] flex items-center gap-4 rounded-xl px-3 py-2 text-muted-foreground hover:text-foreground"
                                >
                                    <Home class="w-5 h-5" />
                                    Dashboard
                                </Link>
                                <Link
                                    href="/products"
                                    class="mx-[-0.65rem] flex items-center gap-4 rounded-xl bg-muted px-3 py-2 text-foreground hover:text-foreground"
                                >
                                    <Package class="w-5 h-5" />
                                    Orders
                                </Link>
                            </nav>
                        </SheetContent>
                    </Sheet>
                </header>
            </div>
            <main class="flex flex-col flex-1 gap-4 p-4 lg:gap-6 lg:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
