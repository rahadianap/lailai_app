<template>
    <div class="min-h-screen bg-background">
        <header class="sticky top-0 z-40 w-full border-b bg-background">
            <div
                class="container flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center space-x-4">
                    <a href="/" class="flex items-center space-x-2">
                        <span class="inline-block font-bold">POS System</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <Button @click="logout" variant="outline" size="sm">
                        Logout
                    </Button>
                </div>
            </div>
        </header>
        <main class="flex-1">
            <slot></slot>
        </main>
    </div>
</template>

<script setup>
import { Button } from "@/components/ui/button";

const logout = () => {
    // Implement logout logic here
    console.log("Logout clicked");
    // For example: clear local storage, redirect to login page, etc.
};
</script>
