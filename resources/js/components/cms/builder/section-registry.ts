import type { Component } from 'vue'
import type { BuilderJsonObject, BuilderViewport, PageSection } from '@/types/cms'
import HeroSection from './sections/HeroSection.vue'
import TextSection from './sections/TextSection.vue'
import ImageSection from './sections/ImageSection.vue'
import CtaSection from './sections/CtaSection.vue'
import CardsSection from './sections/CardsSection.vue'

export type InspectorField = { key: string; label: string; type: 'text' | 'url' | 'textarea' }

export interface SectionDefinition {
  type: string
  label: string
  description: string
  component: Component
  variants: { label: string; value: string }[]
  contentFields: InspectorField[]
  defaults: { variant: string; content: BuilderJsonObject; styles: BuilderJsonObject; responsive: BuilderJsonObject; animation: BuilderJsonObject }
}

const variants = (values: string[]) => values.map((value) => ({ label: value.replace(/(^|-)(\w)/g, (_, __, char) => ` ${char.toUpperCase()}`).trim(), value }))

export const sectionRegistry: Record<string, SectionDefinition> = {
  hero: { type: 'hero', label: 'Hero', description: 'Editorial hero with headline and supporting copy.', component: HeroSection, variants: variants(['default', 'split', 'centered']), contentFields: [{ key: 'title', label: 'Title', type: 'text' }, { key: 'description', label: 'Description', type: 'textarea' }], defaults: { variant: 'default', content: { title: 'Ideas That Inspire.', description: 'Designs That Deliver.' }, styles: {}, responsive: {}, animation: { preset: 'fade-up' } } },
  text: { type: 'text', label: 'Text', description: 'Rich editorial text block.', component: TextSection, variants: variants(['default', 'lead', 'centered']), contentFields: [{ key: 'title', label: 'Title', type: 'text' }, { key: 'description', label: 'Description', type: 'textarea' }], defaults: { variant: 'default', content: { title: 'Tell your story.', description: 'Add supporting content here.' }, styles: {}, responsive: {}, animation: {} } },
  image: { type: 'image', label: 'Image', description: 'Media-led visual section.', component: ImageSection, variants: variants(['default', 'wide', 'full-bleed']), contentFields: [{ key: 'src', label: 'Image URL', type: 'url' }, { key: 'alt', label: 'Alt text', type: 'text' }, { key: 'caption', label: 'Caption', type: 'text' }], defaults: { variant: 'default', content: { src: '', alt: '', caption: '' }, styles: {}, responsive: {}, animation: {} } },
  cta: { type: 'cta', label: 'CTA', description: 'Conversion-focused call to action.', component: CtaSection, variants: variants(['default', 'minimal', 'dark']), contentFields: [{ key: 'title', label: 'Title', type: 'text' }, { key: 'description', label: 'Description', type: 'textarea' }, { key: 'label', label: 'Button label', type: 'text' }, { key: 'href', label: 'Button URL', type: 'url' }], defaults: { variant: 'default', content: { title: 'Start a conversation.', description: 'Tell us what you are building.', label: 'Get in touch', href: '/contact' }, styles: {}, responsive: {}, animation: { preset: 'fade-up' } } },
  cards: { type: 'cards', label: 'Cards', description: 'Flexible grid for services, work or insights.', component: CardsSection, variants: variants(['grid', 'two-column', 'featured']), contentFields: [{ key: 'title', label: 'Title', type: 'text' }], defaults: { variant: 'grid', content: { title: 'Selected work', items: [] }, styles: {}, responsive: {}, animation: {} } },
}

export const getSectionDefinition = (type: string): SectionDefinition | null => sectionRegistry[type] ?? null
export const getSectionComponent = (section: PageSection): Component | null => getSectionDefinition(section.type)?.component ?? null
export const getSectionDefaults = (type: string) => getSectionDefinition(type)?.defaults ?? sectionRegistry.text.defaults
export const viewportWidth = (viewport: BuilderViewport) => ({ desktop: '100%', tablet: '768px', mobile: '390px' })[viewport]
