import { ADD_ONS, DESTINATION_ZONES } from "@/lib/constants";

export type DestinationZone = (typeof DESTINATION_ZONES)[number];
export type AddOn = (typeof ADD_ONS)[number];

export interface TravelerFormData {
  id: string;
  name: string;
  birthDate: string;
  addOns: AddOn[];
}

export interface QuoteRequestPayload {
  destination: DestinationZone;
  start_date: string;
  end_date: string;
  travelers: {
    name: string;
    birth_date: string;
    add_ons?: AddOn[];
  }[];
}

export interface TravelerQuoteResult {
  name: string;
  birth_date?: string;
  add_ons?: AddOn[];
  age: number;
  subtotal: number;
  applied_add_ons: AddOn[];
}

export interface TravelerCalculationBreakdown {
  name: string;
  birth_date: string;
  requested_add_ons: AddOn[];
  age_at_trip_start: number;
  age_multiplier: number;
  age_multiplier_label: string;
  daily_rate: number;
  charged_days: number;
  base_amount: number;
  base_formula: string;
  after_age_multiplier: number;
  after_age_formula: string;
  adventure_sports_requested: boolean;
  adventure_sports_eligible: boolean;
  adventure_sports_amount: number;
  adventure_sports_formula: string | null;
  luggage_requested: boolean;
  luggage_amount: number;
  luggage_formula: string | null;
  raw_subtotal: number;
  rounded_subtotal: number;
  applied_add_ons: AddOn[];
}

export interface QuoteCalculationBreakdown {
  constants: {
    min_charged_days: number;
    luggage_daily_rate: number;
    adventure_sports_rate: number;
    group_discount_threshold: number;
    group_discount_percentage: number;
    adventure_sports_min_age: number;
    adventure_sports_max_age: number;
  };
  trip: {
    destination: string;
    daily_rate: number;
    start_date: string;
    end_date: string;
    trip_days: number;
    charged_days: number;
    min_charged_days: number;
    min_charged_days_applied: boolean;
    charged_days_formula: string;
  };
  travelers: TravelerCalculationBreakdown[];
  summary: {
    travelers_count: number;
    group_subtotal_before_discount: number;
    group_discount_threshold: number;
    group_discount_percentage: number;
    group_discount_amount: number;
    final_total: number;
    rounding: string;
  };
}

export interface QuoteWarningStructured {
  code: string;
  params: Record<string, string | number>;
}

export type QuoteWarning = string | QuoteWarningStructured;

export interface QuoteResponse {
  id?: number;
  destination?: string;
  start_date?: string;
  end_date?: string;
  charged_days: number;
  travelers?: TravelerQuoteResult[];
  travelers_count?: number;
  warnings?: QuoteWarning[];
  group_discount_percentage: number;
  final_total: number;
  created_at?: string;
  calculation_breakdown?: QuoteCalculationBreakdown | null;
}

export type QuoteListItem = Required<
  Pick<
    QuoteResponse,
    | "id"
    | "destination"
    | "start_date"
    | "end_date"
    | "charged_days"
    | "travelers_count"
    | "group_discount_percentage"
    | "final_total"
  >
> &
  Pick<QuoteResponse, "created_at">;

export interface QuoteFormState {
  destination: DestinationZone;
  startDate: string;
  endDate: string;
  travelers: TravelerFormData[];
}
