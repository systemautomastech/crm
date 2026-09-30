import { useCallback, useRef } from 'react'
import axios from 'axios'

import type {
    CallerExtra,
    CallStore,
} from '../store/callStore'

interface CallerLookupResponse {
    found: boolean
    type?: string
    id?: number | string
    name?: string
    phone?: string
    email?: string
    organization?: string | null
    number?: string
    extra?: CallerExtra
}

/**
 * Converts:
 *
 * sip:01733490080@domain.com
 * "John Doe" <sip:01733490080@domain.com>
 * tel:+8801733490080
 *
 * Into:
 *
 * 01733490080
 * +8801733490080
 */
function extractCallerNumber(value: string): string {
    const trimmedValue = value.trim()

    if (!trimmedValue) {
        return ''
    }

    const sipMatch = trimmedValue.match(
        /(?:sip:|tel:)?(\+?\d{3,15})(?:@|>|;|\s|$)/i,
    )

    if (sipMatch?.[1]) {
        return sipMatch[1]
    }

    const cleanedValue = trimmedValue
        .replace(/^sip:/i, '')
        .replace(/^tel:/i, '')
        .split('@')[0]
        .replace(/[<>"'\s()[\]-]/g, '')

    const phoneMatch = cleanedValue.match(/\+?\d+/)

    return phoneMatch?.[0] ?? ''
}

export function useCallerLookup(callStore: CallStore) {
    const lookupRequestId = useRef(0)

    const performCallerLookup = useCallback(
        async (rawPhoneNumber: string): Promise<void> => {
            const callerLookupUrl =
                window.Dialer?.api?.callerLookup

            const phoneNumber =
                extractCallerNumber(rawPhoneNumber)

            if (!callerLookupUrl || !phoneNumber) {
                console.warn(
                    'Caller lookup skipped:',
                    {
                        callerLookupUrl,
                        rawPhoneNumber,
                        phoneNumber,
                    },
                )

                return
            }

            const currentRequestId =
                ++lookupRequestId.current

            try {
                console.log(
                    'Looking up incoming caller:',
                    phoneNumber,
                )

                const { data } =
                    await axios.get<CallerLookupResponse>(
                        callerLookupUrl,
                        {
                            params: {
                                number: phoneNumber,
                            },
                        },
                    )

                /*
                 * Ignore an older request when a newer lookup has started.
                 */
                if (
                    currentRequestId !==
                    lookupRequestId.current
                ) {
                    return
                }

                console.log(
                    'Caller lookup response:',
                    data,
                )

                if (data.found) {
                    callStore.callerInfo = {
                        found: true,
                        type: data.type,
                        id: data.id,
                        name: data.name,
                        phone: data.phone,
                        email: data.email,
                        organization:
                            data.organization ?? undefined,
                        extra: data.extra ?? {},
                    }

                    callStore.contactName =
                        data.name ?? ''

                    return
                }

                callStore.callerInfo = {
                    found: false,
                    number: phoneNumber,
                }

                callStore.contactName = ''
            } catch (error) {
                if (
                    currentRequestId !==
                    lookupRequestId.current
                ) {
                    return
                }

                console.warn(
                    'Caller lookup failed:',
                    error,
                )

                callStore.callerInfo = {
                    found: false,
                    number: phoneNumber,
                }

                callStore.contactName = ''
            }
        },
        [callStore],
    )

    return {
        performCallerLookup,
    }
}