import { ref, computed } from 'vue'
import en from './en'
import es from './es'
import pt from './pt'

type Locale = 'en' | 'es' | 'pt'

const messages: Record<Locale, typeof en> = { en, es, pt }

const currentLocale = ref<Locale>('en')
const isChangingLocale = ref(false)

function getNestedValue(obj: Record<string, unknown>, path: string): string {
  const value = path.split('.').reduce<unknown>((acc, key) => {
    if (acc && typeof acc === 'object') {
      return (acc as Record<string, unknown>)[key]
    }
    return undefined
  }, obj)
  return typeof value === 'string' ? value : path
}

export function useI18n() {
  const t = computed(() => {
    const locale = currentLocale.value
    return (key: string): string => {
      return getNestedValue(messages[locale] as unknown as Record<string, unknown>, key)
    }
  })

  async function setLocale(locale: Locale) {
    isChangingLocale.value = true
    await new Promise((resolve) => setTimeout(resolve, 100))
    currentLocale.value = locale
    isChangingLocale.value = false
  }

  return {
    t: computed(() => t.value),
    locale: currentLocale,
    isChangingLocale,
    setLocale,
  }
}

export const localeToLang: Record<Locale, string> = {
  en: 'en',
  es: 'es',
  pt: 'pt',
}
