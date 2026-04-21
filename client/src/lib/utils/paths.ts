import { base } from '$app/paths';


export function withBase(path: string): string {
    
    const cleanPath = path.startsWith('/') ? path : `/${path}`;
    const cleanBase = base.endsWith('/') ? base.slice(0, -1) : base;
    
    if (cleanPath.startsWith(cleanBase)) {
        return cleanPath;
    }
    
    return `${cleanBase}${cleanPath}`;
}

