import { SelectItem } from '@/components/ui/select';

export const paymentGateway = () => {

    return [{
        id: 'stripe-gateway',
        order: 1500,
        component: (
            <SelectItem value="Stripe">{'Stripe'}</SelectItem>
        )
    }];
};
