export type PaymentStatus = "pending" | "paid" | "overdue" | "cancelled";

export interface QuotePayment {
  id: number;
  quote_id: number;
  status: PaymentStatus;
  value: number;
  quote_total?: number;
  charge_percentage?: number;
  due_date: string;
  pix_encoded_image: string | null;
  pix_payload: string | null;
  pix_expiration_date: string | null;
  paid_at: string | null;
  created_at?: string;
  environment?: "sandbox" | "production";
}

export interface QuotePaymentResponse {
  payment: QuotePayment | null;
}
