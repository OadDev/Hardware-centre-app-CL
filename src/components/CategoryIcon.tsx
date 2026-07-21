import React from 'react';
import { colors } from '../theme/theme';
import {
  IconAdhesive,
  IconAgriculture,
  IconBolt,
  IconConstruction,
  IconDroplet,
  IconFastener,
  IconIndustrial,
  IconMore,
  IconPaint,
  IconSafety,
  IconSanitary,
  IconWrench,
} from './Icons';

export function iconForCat(id: string, size = 25, color: string = colors.bgDark) {
  switch (id) {
    case 'electrical':
      return <IconBolt size={size} color={color} />;
    case 'plumbing':
      return <IconDroplet size={size} color={color} />;
    case 'paint':
      return <IconPaint size={size} color={color} />;
    case 'tools':
      return <IconWrench size={size} color={color} />;
    case 'fasteners':
      return <IconFastener size={size} color={color} />;
    case 'safety':
      return <IconSafety size={size} color={color} />;
    case 'construction':
      return <IconConstruction size={size} color={color} />;
    case 'adhesives':
      return <IconAdhesive size={size} color={color} />;
    case 'sanitary':
      return <IconSanitary size={size} color={color} />;
    case 'agriculture':
      return <IconAgriculture size={size} color={color} />;
    case 'industrial':
      return <IconIndustrial size={size} color={color} />;
    case 'more':
      return <IconMore size={size} color="#fff" />;
    default:
      return <IconWrench size={size} color={color} />;
  }
}
