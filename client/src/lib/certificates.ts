//import {getUserCertificates, Certificate, createDetachedSignature, createHash} from 'crypto-pro-actual-cades-plugin';
import {browser} from '$app/environment';

let module: any = null;

//Требуется для работы в браузере (исключение ошибки Windows not defined)
async function loadModule() {
    if (!browser) {
        throw new Error('Плагин КриптоПро не доступен в SSR');
    }
    if (!module) {
        module = await import('crypto-pro-actual-cades-plugin');
    }
    return module;
}

export async function getCertificates() : Promise<Certificate[]> {
    if (!browser) {
        return [];
    }
    try{
        const {getUserCertificates} = await loadModule();
        return await getUserCertificates();

    } catch (err){
        console.log(err)
        throw err
    }
}

export async function createSignature(data:any, cert : Certificate): Promise<string> {
    if (!browser) {
        throw new Error('Плагин КриптоПро не доступен в SSR');
    }
    try{
        const {createHash, createDetachedSignature} = await loadModule();
        let hash = await createHash(data)
        console.log('hash', hash)
        try{
            let signature = await createDetachedSignature(cert.thumbprint, hash)
            return signature
        } catch (signError) {
            console.log(signError)
            throw signError
        }
    } catch (hashError) {
        console.log(hashError)
        throw hashError
    }
}

export type {Certificate} from 'crypto-pro-actual-cades-plugin';

