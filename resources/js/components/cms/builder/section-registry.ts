import type { Component } from 'vue'
import type { BuilderJsonObject, BuilderViewport, PageSection } from '@/types/cms'
import HeroSection from './sections/HeroSection.vue'
import TextSection from './sections/TextSection.vue'
import ImageSection from './sections/ImageSection.vue'
import CtaSection from './sections/CtaSection.vue'
import CardsSection from './sections/CardsSection.vue'

export interface SectionDefinition {
  type: string
  label: string
  description: string
  component: Component
  defaults: {
    variant: string
    content: BuilderJsonObject
    styles: BuilderJsonObject
    responsive: BuilderJsonObject
    animation: BuilderJsonObject
  }
}

export const sectionRegistry: Record<string, SectionDefinition> = {
  hero: {
    type: 'hero', label: 'Hero', description: 'Editorial hero with headline and supporting copy.', component: HeroSection,
    defaults: { variant: 'default', content: { title: 'Ideas That Inspire.', description: 'Designs That Deliver.' }, styles: {}, responsive: {}, animation: { preset: 'fade-up' } },
  },
  text: {
    type: 'text', label: 'Text', description: 'Rich editorial text block.', component: TextSection,
    defaults: { variant: 'default', content: { title: 'Tell your story.', description: 'Add supporting content here.' }, styles: {}, responsive: {}, animation: {} },
  },
  image: {
    type: 'image', label: 'Image', description: 'Media-led visual section.', component: ImageSection,
    defaults: { variant: 'default', content: { src: '', alt: '', caption: '' }, styles: {}, responsive: {}, animation: {} },
  },
  cta: {
    type: 'cta', label: 'CTA', description: 'Conversion-focused call to action.', component: CtaSection,
    defaults: { variant: 'default', content: { title: 'Start a conversation.', description: 'Tell us what you are building.', label: 'Get in touch', href: '/contact' }, styles: {}, responsive: {}, animation: { preset: 'fade-up' } },
  },
  cards: {
    type: 'cards', label: 'Cards', description: 'Flexible grid for services, work or insights.', component: CardsSection,
    defaults: { variant: 'grid', content: { title: 'Selected work', items: [] }, styles: {}, responsive: {}, animation: {} },
  },
}

export const getSectionDefinition = (type: string): SectionDefinition | null => sectionRegistry[type] ?? null

export const getSectionComponent = (section: PageSection): Component | null => getSectionDefinition(section.type)?.component ?? null

export const getSectionDefaults = (type: string) => getSectionDefinition(type)?.defaults ?? sectionRegistry.text.defaults

export const viewportWidth = (viewport: BuilderViewport) => ({ desktop: '100%', tablet: '768px', mobile: '390px' })[viewport]
