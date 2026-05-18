package com.ecommerce.cod.repository;

import com.ecommerce.cod.model.ZonaEntrega;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface ZonaEntregaRepository extends JpaRepository<ZonaEntrega, Long> {

    Optional<ZonaEntrega> findByNomeBairro(String nomeBairro);

    List<ZonaEntrega> findByAtivaTrueOrderByTaxaEntregaAsc();

    List<ZonaEntrega> findByAtivaTrueOrderByNomeBairroAsc();

    @Query("SELECT z FROM ZonaEntrega z WHERE z.ativa = true AND (LOWER(z.nomeBairro) LIKE LOWER(CONCAT('%', :termo, '%')) OR LOWER(z.descricaoZona) LIKE LOWER(CONCAT('%', :termo, '%')))")
    List<ZonaEntrega> buscarPorTermo(String termo);

    @Query("SELECT z FROM ZonaEntrega z WHERE z.taxaEntrega BETWEEN :min AND :max AND z.ativa = true ORDER BY z.taxaEntrega ASC")
    List<ZonaEntrega> buscarPorFaixaDeTaxa(Double min, Double max);
}
