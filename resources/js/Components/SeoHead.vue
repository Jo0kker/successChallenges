<script setup>
import {Head} from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        required: true
    },
    canonicalUrl: {
        type: String,
        default: null
    },
    ogImage: {
        type: String,
        default: null
    },
    keywords: {
        type: String,
        default: 'Dofus, succès, défis, challenge, jeu, groupe, amis'
    },
    type: {
        type: String,
        default: 'website'
    },
    customMeta: {
        type: Array,
        default: () => []
    }
});

// Construire l'URL canonique
const getCanonicalUrl = () => {
    if (props.canonicalUrl) {
        // Nettoyer l'URL canonique fournie
        return props.canonicalUrl
            .replace(/\/+$/, '') // Enlever les slashes à la fin
            .replace(/\/\?.*$/, ''); // Enlever les paramètres de requête
    }

    return window.location.href
        .replace(/\/+$/, '') // Enlever les slashes à la fin
        .replace(/\/\?.*$/, '');
};

// Générer le JSON-LD pour le site web
const getJsonLd = () => {
    const jsonLd = {
        '@context': 'https://schema.org',
        '@type': props.type === 'website' ? 'WebSite' : props.type,
        'name': props.title,
        'description': props.description,
        'url': getCanonicalUrl()
    };

    if (props.ogImage) {
        jsonLd.image = props.ogImage;
    }

    return JSON.stringify(jsonLd);
};
</script>

<template>
    <Head>
        <!-- Balises de base -->
        <title>{{ title }}</title>
        <meta name="description" :content="description" />
        <meta name="keywords" :content="keywords" />
        <link rel="canonical" :href="getCanonicalUrl()" />

        <!-- Open Graph -->
        <meta property="og:title" :content="title" />
        <meta property="og:description" :content="description" />
        <meta property="og:type" :content="type" />
        <meta property="og:url" :content="getCanonicalUrl()" />
        <meta property="og:site_name" content="Dofus Success Challenge" />
        <meta v-if="ogImage" property="og:image" :content="ogImage" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="title" />
        <meta name="twitter:description" :content="description" />
        <meta v-if="ogImage" name="twitter:image" :content="ogImage" />

        <!-- Structured Data (JSON-LD) -->
        <script type="application/ld+json" v-html="getJsonLd()"></script>

        <!-- Autres balises meta -->
        <meta name="robots" content="index, follow" />
        <meta name="language" content="fr" />
        <meta name="revisit-after" content="7 days" />
        <meta name="author" content="Dofus Success Challenge" />
        <meta name="geo.region" content="FR" />
        <meta name="geo.placename" content="France" />

        <!-- Custom Meta Tags -->
        <template v-for="(meta, index) in customMeta" :key="index">
            <meta :name="meta.name" :content="meta.content" v-if="meta.name" />
            <meta :property="meta.property" :content="meta.content" v-else-if="meta.property" />
        </template>
    </Head>
</template>
