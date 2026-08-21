import React from 'react';
import Svg, { Path, Circle, Rect, G } from 'react-native-svg';

export type IconProps = {
  size?: number;
  color?: string;
  strokeWidth?: number;
};

const base = (size = 20) => ({ width: size, height: size, viewBox: '0 0 24 24' });

export function IconLocation({ size = 14, color = '#F97316', strokeWidth = 2 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth}>
      <Path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z" />
      <Circle cx="12" cy="10" r="2.2" />
    </Svg>
  );
}

export function IconChevronDown({ size = 14, color = '#6B7280', strokeWidth = 2 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="m6 9 6 6 6-6" />
    </Svg>
  );
}

export function IconChevronLeft({ size = 20, color = '#111827', strokeWidth = 2 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="M15 18l-6-6 6-6" />
    </Svg>
  );
}

export function IconChevronRight({ size = 18, color = '#9CA3AF', strokeWidth = 2 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="m9 18 6-6-6-6" />
    </Svg>
  );
}

export function IconBell({ size = 21, color = '#1F2937', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
      <Path d="M13.7 21a2 2 0 0 1-3.4 0" />
    </Svg>
  );
}

export function IconSearch({ size = 20, color = '#6B7280', strokeWidth = 2 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth}>
      <Circle cx="11" cy="11" r="7" />
      <Path d="m20 20-3.2-3.2" strokeLinecap="round" />
    </Svg>
  );
}

export function IconMic({ size = 19, color = '#F97316', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Rect x="9" y="3" width="6" height="11" rx="3" />
      <Path d="M5 11a7 7 0 0 0 14 0M12 18v3" />
    </Svg>
  );
}

export function IconBolt({ size = 25, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z" />
    </Svg>
  );
}

export function IconBoltFilled({ size = 13, color = '#F97316' }: IconProps) {
  return (
    <Svg {...base(size)} fill={color}>
      <Path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z" />
    </Svg>
  );
}

export function IconDroplet({ size = 25, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth}>
      <Path d="M12 3s6 6.5 6 10.5a6 6 0 0 1-12 0C6 9.5 12 3 12 3z" />
    </Svg>
  );
}

export function IconPaint({ size = 25, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Rect x="3" y="4" width="13" height="6" rx="1.5" />
      <Path d="M16 7h3a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-7" />
      <Path d="M11 11v3a1 1 0 0 0 1 1 1 1 0 0 1 1 1v4" />
    </Svg>
  );
}

export function IconWrench({ size = 25, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Path d="M15.5 4a4 4 0 0 0-5 5L4 15.5 8.5 20l6.5-6.5a4 4 0 0 0 5-5l-2.7 2.7-2.3-.5-.5-2.3z" />
    </Svg>
  );
}

export function IconFastener({ size = 25, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Path d="M9 3h6l-1 4h-4z" />
      <Path d="M10 7h4v13l-2 2-2-2z" />
      <Path d="M10 11h4M10 15h4" />
    </Svg>
  );
}

export function IconSafety({ size = 25, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Path d="M4 15a8 8 0 0 1 16 0" />
      <Rect x="2.5" y="15" width="19" height="4" rx="1.5" />
      <Path d="M12 7v3M9.5 7.5h5" />
    </Svg>
  );
}

export function IconConstruction({ size = 25, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Rect x="4" y="3" width="16" height="18" rx="1.5" />
      <Path d="M8.5 7h2M13.5 7h2M8.5 11h2M13.5 11h2M8.5 15h2M13.5 15h2" />
    </Svg>
  );
}

export function IconAdhesive({ size = 23, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Path d="M9 3h6v3l1.5 12a2 2 0 0 1-2 2h-5a2 2 0 0 1-2-2L9 6z" />
      <Path d="M9 6h6" />
    </Svg>
  );
}

export function IconSanitary({ size = 23, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Path d="M3 8h7v4H3z" />
      <Path d="M10 9h5a3 3 0 0 1 3 3v1" />
      <Path d="M18 14v4" />
    </Svg>
  );
}

export function IconAgriculture({ size = 23, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Path d="M12 21v-7" />
      <Path d="M12 14c0-4 3-7 7-7 0 4-3 7-7 7z" />
      <Path d="M12 16c0-3-2.5-5-6-5 0 3 2.5 5 6 5z" />
    </Svg>
  );
}

export function IconIndustrial({ size = 23, color = '#1F2937', strokeWidth = 1.6 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Circle cx="12" cy="12" r="3.2" />
      <Path d="M12 3v2.5M12 18.5V21M21 12h-2.5M5.5 12H3M18 6l-1.8 1.8M7.8 16.2 6 18M18 18l-1.8-1.8M7.8 7.8 6 6" />
    </Svg>
  );
}

export function IconMore({ size = 23, color = '#fff' }: IconProps) {
  return (
    <Svg {...base(size)} fill={color}>
      <Circle cx="6" cy="12" r="2" />
      <Circle cx="12" cy="12" r="2" />
      <Circle cx="18" cy="12" r="2" />
    </Svg>
  );
}

export function IconStar({ size = 12, color = '#F59E0B' }: IconProps) {
  return (
    <Svg {...base(size)} fill={color}>
      <Path d="m12 2 3 6.5 7 .6-5.3 4.6L18.4 21 12 17.3 5.6 21l1.7-7.3L2 9.1l7-.6L12 2Z" />
    </Svg>
  );
}

export function IconHeart({ size = 16, color = 'none', stroke = '#9CA3AF', strokeWidth = 2 }: IconProps & { color?: string; stroke?: string }) {
  return (
    <Svg {...base(size)} fill={color} stroke={stroke} strokeWidth={strokeWidth}>
      <Path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
    </Svg>
  );
}

export function IconPlus({ size = 17, color = '#fff', strokeWidth = 2.4 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round">
      <Path d="M12 5v14M5 12h14" />
    </Svg>
  );
}

export function IconMinus({ size = 18, color = '#374151' }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={2.4} strokeLinecap="round">
      <Path d="M5 12h14" />
    </Svg>
  );
}

export function IconCart({ size = 21, color = '#1F2937', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Circle cx="9" cy="20" r="1.4" />
      <Circle cx="18" cy="20" r="1.4" />
      <Path d="M2 3h2.2l2 12.2a1.5 1.5 0 0 0 1.5 1.3H18a1.5 1.5 0 0 0 1.5-1.2L21 7H5" />
    </Svg>
  );
}

export function IconOrders({ size = 23, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="M6 2h9l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z" />
      <Path d="M9 8h6M9 12h6M9 16h4" />
    </Svg>
  );
}

export function IconUser({ size = 23, color = '#6B7280', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Circle cx="12" cy="8" r="4" />
      <Path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5" />
    </Svg>
  );
}

export function IconHome({ size = 23, color = '#6B7280', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="M3 10.5 12 3l9 7.5" />
      <Path d="M5 9.5V21h14V9.5" />
    </Svg>
  );
}

export function IconGrid({ size = 23, color = '#6B7280', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth}>
      <Rect x="3" y="3" width="7" height="7" rx="1.5" />
      <Rect x="14" y="3" width="7" height="7" rx="1.5" />
      <Rect x="3" y="14" width="7" height="7" rx="1.5" />
      <Rect x="14" y="14" width="7" height="7" rx="1.5" />
    </Svg>
  );
}

export function IconListView({ size = 16, color = '#6B7280', strokeWidth = 2 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round">
      <Path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
    </Svg>
  );
}

export function IconFilter({ size = 15, color = '#374151', strokeWidth = 2 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round">
      <Path d="M3 5h18M6 12h12M10 19h4" />
    </Svg>
  );
}

export function IconVerified({ size = 15, color = '#22C55E' }: IconProps) {
  return (
    <Svg {...base(size)} fill={color}>
      <Path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
      <Path d="m8.5 12 2.3 2.3 4.7-4.8" stroke="#fff" strokeWidth={2} fill="none" strokeLinecap="round" strokeLinejoin="round" />
    </Svg>
  );
}

export function IconTruck({ size = 18, color = '#1F2937', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Rect x="1" y="6" width="13" height="11" rx="1.5" />
      <Path d="M14 9h4l3 3v5h-7" />
      <Circle cx="6" cy="18" r="1.6" />
      <Circle cx="17" cy="18" r="1.6" />
    </Svg>
  );
}

export function IconCheckCircle({ size = 18, color = '#16A34A', strokeWidth = 2 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="M20 6 9 17l-5-5" />
    </Svg>
  );
}

export function IconClock({ size = 14, color = '#9CA3AF', strokeWidth = 2 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth}>
      <Circle cx="12" cy="12" r="9" />
      <Path d="M12 7v5l3 2" strokeLinecap="round" />
    </Svg>
  );
}

export function IconTicket({ size = 20, color = '#F97316', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Path d="M3 9V6a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v3a2 2 0 0 0 0 6v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-3a2 2 0 0 0 0-6Z" />
      <Path d="M13 7v10" strokeDasharray="2 2" />
    </Svg>
  );
}

export function IconMail({ size = 19, color = '#6B7280', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth}>
      <Rect x="3" y="5" width="18" height="14" rx="2" />
      <Path d="m3 7 9 6 9-6" />
    </Svg>
  );
}

export function IconPhone({ size = 19, color = '#6B7280', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth}>
      <Path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
    </Svg>
  );
}

export function IconWhatsapp({ size = 19, color = '#25D366' }: IconProps) {
  return (
    <Svg {...base(size)} fill={color} stroke="none">
      <Path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.39 1.26 4.81L2 22l5.35-1.4a9.9 9.9 0 0 0 4.69 1.19h.01c5.46 0 9.9-4.45 9.9-9.91C21.96 6.45 17.5 2 12.04 2m0 18.15c-1.47 0-2.9-.39-4.14-1.13l-.3-.18-3.08.81.82-3-.19-.31a8.2 8.2 0 0 1-1.26-4.42c0-4.54 3.7-8.24 8.25-8.24a8.19 8.19 0 0 1 8.24 8.24c0 4.55-3.7 8.24-8.24 8.24m4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.78.97-.14.17-.29.19-.53.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.38-1.72-.15-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.13-.15.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.87.85-.87 2.08 0 1.22.89 2.4 1.01 2.57.13.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.16-.48-.28" />
    </Svg>
  );
}

export function IconLock({ size = 19, color = '#6B7280', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth}>
      <Rect x="4" y="11" width="16" height="10" rx="2" />
      <Path d="M8 11V7a4 4 0 0 1 8 0v4" />
    </Svg>
  );
}

export function IconGoogle({ size = 19 }: IconProps) {
  return (
    <Svg width={size} height={size} viewBox="0 0 48 48">
      <Path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.4 29.2 35 24 35c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.5 5 29.5 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21 21-9.4 21-21c0-1.2-.1-2.3-.4-3.5z" />
      <Path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 16 19 13 24 13c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.5 7 29.5 5 24 5 16 5 9.1 9.5 6.3 14.7z" />
      <Path fill="#4CAF50" d="M24 45c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 36 26.7 37 24 37c-5.2 0-9.6-2.6-11.3-6.9l-6.5 5C9 41.4 15.9 45 24 45z" />
      <Path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.1 5.6l6.2 5.2C42.9 35.7 45 30.3 45 24c0-1.2-.1-2.3-.4-3.5z" />
    </Svg>
  );
}

export function IconLogout({ size = 18, color = '#EF4444', strokeWidth = 1.9 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
      <Path d="m16 17 5-5-5-5M21 12H9" />
    </Svg>
  );
}

export function IconEdit({ size = 17, color = '#fff', strokeWidth = 1.9 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="M12 20h9" />
      <Path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
    </Svg>
  );
}

export function IconWishlist({ size = 19, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth}>
      <Path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
    </Svg>
  );
}

export function IconAddress({ size = 19, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z" />
      <Circle cx="12" cy="10" r="2.4" />
    </Svg>
  );
}

export function IconGst({ size = 19, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Rect x="3" y="5" width="18" height="14" rx="2" />
      <Path d="M7 9h4M7 13h7" />
    </Svg>
  );
}

export function IconSettings({ size = 19, color = '#1F2937', strokeWidth = 1.6 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Circle cx="12" cy="12" r="3.2" />
      <Path d="M12 3v2.5M12 18.5V21M21 12h-2.5M5.5 12H3M18 6l-1.8 1.8M7.8 16.2 6 18M18 18l-1.8-1.8M7.8 7.8 6 6" />
    </Svg>
  );
}

export function IconHelp({ size = 19, color = '#1F2937', strokeWidth = 1.7 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Circle cx="12" cy="12" r="9" />
      <Path d="M9.5 9a2.5 2.5 0 0 1 5 0c0 1.7-2.5 2-2.5 4" />
      <Path d="M12 17h.01" />
    </Svg>
  );
}

export function IconShare({ size = 19, color = '#111827', strokeWidth = 1.8 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Circle cx="18" cy="5" r="3" />
      <Circle cx="6" cy="12" r="3" />
      <Circle cx="18" cy="19" r="3" />
      <Path d="m8.6 13.5 6.8 4M15.4 6.5 8.6 10.5" />
    </Svg>
  );
}

export function IconSupplier({ size = 22, color = '#1F2937', strokeWidth = 1.6 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinejoin="round">
      <Path d="M3 21V8l6-3 6 3v13" />
      <Path d="M15 11h6v10h-6" />
      <Path d="M6 11h3M6 14h3M6 17h3M18 14h0M18 17h0" />
    </Svg>
  );
}

export function IconTrash({ size = 18, color = '#9CA3AF', strokeWidth = 2 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round">
      <Path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" />
    </Svg>
  );
}

export function IconEmptyOrders({ size = 44, color = '#CBD5E1', strokeWidth = 1.4 }: IconProps) {
  return (
    <Svg {...base(size)} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round">
      <Path d="M6 2h9l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z" />
      <Path d="M9 8h6M9 12h6M9 16h4" />
    </Svg>
  );
}

export function IconPlay({ size = 16, color = '#fff' }: IconProps) {
  return (
    <Svg {...base(size)} fill={color}>
      <Path d="M8 5v14l11-7z" />
    </Svg>
  );
}
