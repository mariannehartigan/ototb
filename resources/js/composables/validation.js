import { reactive, ref } from 'vue'

export function useFormValidation() {
  const errors = reactive({
    name: '',
    email: '',
    password: '',
    description: '',
  })

  const submitted = ref(false)

  const isEmail = (value) =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)

  const rules = {
    name: [
      (v) => (!v?.trim() ? 'Name is required' : ''),
    ],
    email: [
      (v) => (!v?.trim() ? 'Email is required' : ''),
      (v) => (!isEmail(v) ? 'Please enter a valid email' : ''),
    ],
    password: [
      (v) => (!v?.trim() ? 'Password is required' : ''),
      (v) => (v.length < 8 ? 'Password must be at least 8 characters' : ''),
    ],
    description: [
      (v) => (!v?.trim() ? 'Description is required' : ''),
    ],
  }

  // Validate a single field
  const validateField = (field, form) => {
    errors[field] = ''
    const value = form[field]
    for (let rule of rules[field] || []) {
      const error = rule(value, form)
      if (error) {
        errors[field] = error
        break
      }
    }
    return !errors[field]
  }

  // Validate entire form and set submitted = true
  const validateForm = (form, fields = null) => {
    submitted.value = true
    let isValid = true
    const fieldsToValidate = fields || Object.keys(rules)
    for (let field of fieldsToValidate) {
      if (!validateField(field, form)) isValid = false
    }
    return isValid
  }

  const clearError = (field) => {
    errors[field] = ''
  }

  return {
    errors,
    submitted,
    validateField,
    validateForm,
    clearError,
  }
}