export type ToastType = 'success' | 'error' | 'info' | 'warning'

export interface ToastOptions {
  message: string
  type?: ToastType
  duration?: number
}

export function showToast({ message, type = 'info', duration = 3000 }: ToastOptions) {
  const toast = document.createElement('div')
  
  const bgColors = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    info: 'bg-blue-500',
    warning: 'bg-yellow-500'
  }
  
  const icons = {
    success: '✓',
    error: '✕',
    info: 'ℹ',
    warning: '⚠'
  }
  
  toast.className = `fixed top-4 right-4 ${bgColors[type]} text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center gap-3 animate-slide-in`
  toast.innerHTML = `
    <span class="text-xl font-bold">${icons[type]}</span>
    <span class="font-medium">${message}</span>
  `
  
  document.body.appendChild(toast)
  
  setTimeout(() => {
    toast.classList.add('animate-slide-out')
    setTimeout(() => toast.remove(), 300)
  }, duration)
}

// Add animation styles if not already present
if (!document.getElementById('toast-styles')) {
  const style = document.createElement('style')
  style.id = 'toast-styles'
  style.textContent = `
    @keyframes slide-in {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    @keyframes slide-out {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(100%);
        opacity: 0;
      }
    }
    
    .animate-slide-in {
      animation: slide-in 0.3s ease-out;
    }
    
    .animate-slide-out {
      animation: slide-out 0.3s ease-in;
    }
  `
  document.head.appendChild(style)
}

