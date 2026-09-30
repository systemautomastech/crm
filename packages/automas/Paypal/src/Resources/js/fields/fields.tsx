import { SelectItem } from '@/components/ui/select';

export const paymentGateway = () => {

    return [{
        id: 'paypal-gateway',
        order: 1510,
        component: (
            <SelectItem value="Paypal">{'Paypal'}</SelectItem>
        )
    }];
};
