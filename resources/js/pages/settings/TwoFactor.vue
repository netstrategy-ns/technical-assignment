<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TwoFactorContent from '@/components/custom/UserProfile/TwoFactorContent.vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

type Props = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});
</script>

<template>
    <ApplicationLayout>
        <Head title="Autenticazione a due fattori" />

        <div class="w-full px-4 py-8">
            <div class="mx-auto max-w-4xl">
                <SettingsLayout>
                    <section class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6">
                        <TwoFactorContent
                            :requires-confirmation="props.requiresConfirmation"
                            :two-factor-enabled="props.twoFactorEnabled"
                        />
                    </section>
                </SettingsLayout>
            </div>
        </div>
    </ApplicationLayout>
</template>
