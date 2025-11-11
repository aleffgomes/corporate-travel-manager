export const formatDate = (date: string | Date): string => {
  if (typeof date === 'string') {
    const [year, month, day] = date.split('-').map(Number)
    const localDate = new Date(year, month - 1, day)
    return localDate.toLocaleDateString('pt-BR')
  }
  return new Date(date).toLocaleDateString('pt-BR')
}

export const formatDateTime = (date: string | Date): string => {
  if (typeof date === 'string' && date.includes('T')) {
    return new Date(date).toLocaleString('pt-BR')
  }
  if (typeof date === 'string') {
    const [year, month, day] = date.split('-').map(Number)
    const localDate = new Date(year, month - 1, day)
    return localDate.toLocaleString('pt-BR')
  }
  return new Date(date).toLocaleString('pt-BR')
}

export const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

export const truncate = (text: string, length: number): string => {
  return text.length > length ? text.substring(0, length) + '...' : text
}

export const dateToString = (date: Date): string => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export const stringToDate = (dateString: string): Date => {
  const [year, month, day] = dateString.split('-').map(Number)
  return new Date(year, month - 1, day)
}
